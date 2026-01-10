<?php
/**
 * Test class for Blitz_Cache_Cache
 */

namespace BlitzCache\Tests\Unit;

use BlitzCache\Tests\BlitzCacheTestCase;
use Brain\Monkey\Functions;
use \Blitz_Cache_Cache;

/**
 * Test suite for Blitz_Cache_Cache class
 */
class CacheTest extends BlitzCacheTestCase
{

    /**
     * Test that cache directory is properly set
     */
    public function testCacheDirectoryIsSet()
    {
        $cache = new Blitz_Cache_Cache();

        $reflection = new \ReflectionClass($cache);
        $property = $reflection->getProperty('cache_dir');
        $property->setAccessible(true);

        $this->assertEquals($this->test_cache_dir . 'pages/', $property->getValue($cache));
    }

    /**
     * Test get_cache_key generates MD5 hash
     */
    public function testGetCacheKeyGeneratesMd5Hash()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test-page/';

        $cache = new Blitz_Cache_Cache();
        $key = $cache->get_cache_key();

        $this->assertEquals(32, strlen($key));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key);
    }

    /**
     * Test get_cache_key includes mobile suffix when enabled
     */
    public function testGetCacheKeyIncludesMobileSuffix()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test-page/';

        // Mock wp_is_mobile to return true
        Functions\when('wp_is_mobile')->justReturn(true);

        // Override options to enable mobile cache
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => true,
            'mobile_cache' => true,
        ]);

        $cache = new Blitz_Cache_Cache();
        $key = $cache->get_cache_key();

        // The key should be MD5 of URL + |mobile
        $expected = md5('http://example.com/test-page/|mobile');
        $this->assertEquals($expected, $key);
    }

    /**
     * Test store method creates cache files
     */
    public function testStoreCreatesCacheFiles()
    {
        $key = 'test_key_' . md5(uniqid());
        $html = '<html><body>Test Content</body></html>';

        $cache = new Blitz_Cache_Cache();
        $result = $cache->store($key, $html);

        $this->assertTrue($result);
        $this->assertFileExists($this->test_cache_dir . 'pages/' . $key . '.html');

        // Cleanup
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html');
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html.gz');
    }

    /**
     * Test delete method removes cache files
     */
    public function testDeleteRemovesCacheFiles()
    {
        $key = 'test_key_' . md5(uniqid());

        // Create test files
        file_put_contents($this->test_cache_dir . 'pages/' . $key . '.html', 'test');
        file_put_contents($this->test_cache_dir . 'pages/' . $key . '.html.gz', 'test gzip');

        $cache = new Blitz_Cache_Cache();
        $cache->delete($key);

        // Check if files are deleted
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/' . $key . '.html');
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/' . $key . '.html.gz');
    }

    /**
     * Test purge_url method deletes specific URL cache
     */
    public function testPurgeUrlDeletesSpecificUrlCache()
    {
        $url = 'http://example.com/test-page/';
        $key = md5($url);
        $mobile_key = md5($url . '|mobile');

        // Create test files
        file_put_contents($this->test_cache_dir . 'pages/' . $key . '.html', 'test');
        file_put_contents($this->test_cache_dir . 'pages/' . $mobile_key . '.html', 'mobile test');

        $cache = new Blitz_Cache_Cache();
        $cache->purge_url($url);

        // Check if both files are deleted (desktop and mobile)
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/' . $key . '.html');
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/' . $mobile_key . '.html');
    }

    /**
     * Test purge_all method clears all cache
     */
    public function testPurgeAllClearsAllCache()
    {
        $meta_file = $this->test_cache_dir . 'meta.json';

        // Create test files
        file_put_contents($this->test_cache_dir . 'pages/key1.html', 'test1');
        file_put_contents($this->test_cache_dir . 'pages/key2.html', 'test2');
        file_put_contents($this->test_cache_dir . 'pages/key3.html.gz', 'test3');
        file_put_contents($meta_file, json_encode(['key1' => [], 'key2' => []]));

        $cache = new Blitz_Cache_Cache();
        $cache->purge_all();

        // Check if all files are deleted
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/key1.html');
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/key2.html');
        $this->assertFileDoesNotExist($this->test_cache_dir . 'pages/key3.html.gz');

        // Check if meta is reset
        $this->assertFileExists($meta_file);
        $meta = json_decode(file_get_contents($meta_file), true);
        $this->assertEmpty($meta);
    }

    /**
     * Test get_stats returns array with stats
     */
    public function testGetStatsReturnsStatsArray()
    {
        $stats_file = $this->test_cache_dir . 'stats.json';

        // Create stats file
        $stats_data = [
            'hits' => 100,
            'misses' => 50,
            'cached_pages' => 25,
            'cache_size' => 1024000,
            'last_warmup' => time(),
            'last_purge' => time(),
            'period_start' => time()
        ];
        file_put_contents($stats_file, json_encode($stats_data));

        $cache = new Blitz_Cache_Cache();
        $stats = $cache->get_stats();

        $this->assertIsArray($stats);
        $this->assertEquals(100, $stats['hits']);
        $this->assertEquals(50, $stats['misses']);
        $this->assertEquals(25, $stats['cached_pages']);
    }

    /**
     * Test store with gzip enabled creates gzipped file
     */
    public function testStoreWithGzipCreatesGzippedFile()
    {
        if (!function_exists('gzencode')) {
            $this->markTestSkipped('gzencode function not available');
        }

        $key = 'test_gzip_' . md5(uniqid());
        $html = '<html><body>Test Content for Gzip</body></html>';

        // Enable gzip
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'gzip_enabled' => true,
            'html_minify_enabled' => false,
            'mobile_cache' => false
        ]);

        $cache = new Blitz_Cache_Cache();
        $result = $cache->store($key, $html);

        $this->assertTrue($result);
        $this->assertFileExists($this->test_cache_dir . 'pages/' . $key . '.html');
        $this->assertFileExists($this->test_cache_dir . 'pages/' . $key . '.html.gz');

        // Verify gzipped content is valid
        $gzipped_content = file_get_contents($this->test_cache_dir . 'pages/' . $key . '.html.gz');
        $decompressed = @gzdecode($gzipped_content);
        $this->assertNotFalse($decompressed);

        // Cleanup
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html');
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html.gz');
    }

    /**
     * Test get_cached returns null for non-existent cache
     */
    public function testGetCachedReturnsNullForNonExistentCache()
    {
        $key = 'non_existent_' . md5(uniqid());

        $cache = new Blitz_Cache_Cache();
        $result = $cache->get_cached($key);

        $this->assertNull($result);
    }

    /**
     * Test get_cached returns cached content
     */
    public function testGetCachedReturnsCachedContent()
    {
        $key = 'cached_' . md5(uniqid());
        $html = '<html><body>Cached Content</body></html>';

        // Manually create a valid cache file with meta
        file_put_contents($this->test_cache_dir . 'pages/' . $key . '.html', $html);

        // Create meta file
        $meta = [
            $key => [
                'url' => 'http://example.com/test/',
                'file' => $key . '.html',
                'created' => time(),
                'expires' => time() + 3600, // Expires in 1 hour
                'mobile' => false,
            ]
        ];
        file_put_contents($this->test_cache_dir . 'meta.json', json_encode($meta));

        $cache = new Blitz_Cache_Cache();
        $result = $cache->get_cached($key);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Cached Content', $result);

        // Cleanup
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html');
        @unlink($this->test_cache_dir . 'meta.json');
    }

    /**
     * Test get_cached returns null for expired cache
     */
    public function testGetCachedReturnsNullForExpiredCache()
    {
        $key = 'expired_' . md5(uniqid());
        $html = '<html><body>Expired Content</body></html>';

        // Manually create a cache file
        file_put_contents($this->test_cache_dir . 'pages/' . $key . '.html', $html);

        // Create meta file with expired timestamp
        $meta = [
            $key => [
                'url' => 'http://example.com/test/',
                'file' => $key . '.html',
                'created' => time() - 7200,
                'expires' => time() - 3600, // Expired 1 hour ago
                'mobile' => false,
            ]
        ];
        file_put_contents($this->test_cache_dir . 'meta.json', json_encode($meta));

        $cache = new Blitz_Cache_Cache();
        $result = $cache->get_cached($key);

        $this->assertNull($result);

        // Cleanup
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html');
        @unlink($this->test_cache_dir . 'meta.json');
    }

    /**
     * Test atomic file write operation
     */
    public function testAtomicFileWrite()
    {
        $key = 'atomic_' . md5(uniqid());
        $html1 = '<html><body>Version 1</body></html>';
        $html2 = '<html><body>Version 2</body></html>';

        $cache = new Blitz_Cache_Cache();
        $cache->store($key, $html1);

        // Immediately overwrite
        $cache->store($key, $html2);

        // Verify the file was overwritten by reading it directly
        $cachedFile = $this->test_cache_dir . 'pages/' . $key . '.html';
        $this->assertFileExists($cachedFile);
        $result = file_get_contents($cachedFile);
        $this->assertStringContainsString('Version 2', $result);

        // Cleanup
        @unlink($this->test_cache_dir . 'pages/' . $key . '.html');
        @unlink($this->test_cache_dir . 'meta.json');
    }

    /**
     * Test meta file operations
     */
    public function testMetaFileOperations()
    {
        $key = 'meta_test_' . md5(uniqid());
        $html = '<html><body>Meta Test</body></html>';

        $cache = new Blitz_Cache_Cache();
        $cache->store($key, $html);

        // Check meta file exists
        $this->assertFileExists($this->test_cache_dir . 'meta.json');

        $meta = json_decode(file_get_contents($this->test_cache_dir . 'meta.json'), true);
        $this->assertArrayHasKey($key, $meta);
        $this->assertArrayHasKey('expires', $meta[$key]);
        $this->assertArrayHasKey('created', $meta[$key]);

        // Delete should also remove meta
        $cache->delete($key);

        $meta_after = json_decode(file_get_contents($this->test_cache_dir . 'meta.json'), true);
        $this->assertArrayNotHasKey($key, $meta_after);

        // Cleanup
        @unlink($this->test_cache_dir . 'meta.json');
    }
}
