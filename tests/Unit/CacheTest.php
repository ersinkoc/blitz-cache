<?php
/**
 * Test class for Blitz_Cache_Cache
 */

namespace BlitzCache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use BlitzCache\Blitz_Cache_Cache;

/**
 * Test suite for Blitz_Cache_Cache class
 */
class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey::setUp();

        // Define necessary global functions
        Functions\when('__')->returnArg();
        Functions\when('esc_html__')->returnArg();
        Functions\when('esc_html_e')->returnArg();
        Functions\when('esc_attr__')->returnArg();
        Functions\when('esc_attr')->returnArg();
        Functions\when('wp_json_encode')->returnArg();
        Functions\when('do_action')->returnArg();
        Functions\when('apply_filters')->returnArg();
        Functions\when('is_ssl')->returnFalse();
        Functions\when('wp_is_mobile')->returnFalse();
        Functions\when('is_user_logged_in')->returnFalse();
    }

    protected function tearDown(): void
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    /**
     * Test that cache directory is properly set
     */
    public function testCacheDirectoryIsSet()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $cache = new Blitz_Cache_Cache();

        $reflection = new \ReflectionClass($cache);
        $property = $reflection->getProperty('cache_dir');
        $property->setAccessible(true);

        $this->assertEquals('/tmp/cache/pages/', $property->getValue($cache));
    }

    /**
     * Test should_cache returns false when disabled
     */
    public function testShouldCacheReturnsFalseWhenDisabled()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        // Mock options
        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => false
        ]);

        $cache = new Blitz_Cache_Cache();
        $this->assertFalse($cache->should_cache());
    }

    /**
     * Test should_cache returns false for POST requests
     */
    public function testShouldCacheReturnsFalseForPost()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true
        ]);

        $cache = new Blitz_Cache_Cache();
        $this->assertFalse($cache->should_cache());
    }

    /**
     * Test should_cache returns false for logged in users (default)
     */
    public function testShouldCacheReturnsFalseForLoggedInUsers()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        Functions\when('is_user_logged_in')->returnTrue();

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'cache_logged_in' => false
        ]);

        $cache = new Blitz_Cache_Cache();
        $this->assertFalse($cache->should_cache());
    }

    /**
     * Test should_cache returns true for logged in users when enabled
     */
    public function testShouldCacheReturnsTrueForLoggedInUsersWhenEnabled()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        Functions\when('is_user_logged_in')->returnTrue();

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'cache_logged_in' => true
        ]);

        $cache = new Blitz_Cache_Cache();
        $this->assertTrue($cache->should_cache());
    }

    /**
     * Test should_cache respects excluded URLs
     */
    public function testShouldCacheRespectsExcludedUrls()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/admin/';

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'excluded_urls' => ['/admin/*', '/wp-admin/*']
        ]);

        $cache = new Blitz_Cache_Cache();
        $this->assertFalse($cache->should_cache());
    }

    /**
     * Test should_cache respects excluded cookies
     */
    public function testShouldCacheRespectsExcludedCookies()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_COOKIE['wordpress_logged_in_123'] = 'user_123';

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'excluded_cookies' => ['wordpress_logged_in_*']
        ]);

        $cache = new Blitz_Cache_Cache();
        $this->assertFalse($cache->should_cache());
    }

    /**
     * Test get_cache_key generates MD5 hash
     */
    public function testGetCacheKeyGeneratesMd5Hash()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test-page/';

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'mobile_cache' => false
        ]);

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
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test-page/';
        Functions\when('wp_is_mobile')->returnTrue();

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'mobile_cache' => true
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
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $cache_dir = '/tmp/cache/pages/';
        $key = 'test_key_123';
        $html = '<html><body>Test Content</body></html>';

        // Create cache directory
        @mkdir($cache_dir, 0755, true);

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'gzip_enabled' => true,
            'html_minify_enabled' => false,
            'mobile_cache' => false
        ]);

        Functions\when('apply_filters')->returnArg();
        Functions\when('do_action')->returnArg();

        // Mock get_current_url
        Functions\when('is_ssl')->returnFalse();
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test/';

        $cache = new Blitz_Cache_Cache();
        $cache->store($key, $html);

        // Check if files exist
        $this->assertFileExists($cache_dir . $key . '.html');
        $this->assertFileExists($cache_dir . $key . '.html.gz');

        // Cleanup
        @unlink($cache_dir . $key . '.html');
        @unlink($cache_dir . $key . '.html.gz');
        @rmdir($cache_dir);
    }

    /**
     * Test delete method removes cache files
     */
    public function testDeleteRemovesCacheFiles()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $cache_dir = '/tmp/cache/pages/';
        $key = 'test_key_456';

        // Create cache directory and files
        @mkdir($cache_dir, 0755, true);
        file_put_contents($cache_dir . $key . '.html', 'test');
        file_put_contents($cache_dir . $key . '.html.gz', 'test gzip');

        $cache = new Blitz_Cache_Cache();
        $cache->delete($key);

        // Check if files are deleted
        $this->assertFileDoesNotExist($cache_dir . $key . '.html');
        $this->assertFileDoesNotExist($cache_dir . $key . '.html.gz');

        // Cleanup
        @rmdir($cache_dir);
    }

    /**
     * Test purge_url method deletes specific URL cache
     */
    public function testPurgeUrlDeletesSpecificUrlCache()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $cache_dir = '/tmp/cache/pages/';
        $url = 'http://example.com/test-page/';

        // Create cache directory and files
        @mkdir($cache_dir, 0755, true);
        file_put_contents($cache_dir . md5($url) . '.html', 'test');
        file_put_contents($cache_dir . md5($url) . '|mobile' . '.html', 'mobile test');

        Functions\when('do_action')->returnArg();

        $cache = new Blitz_Cache_Cache();
        $cache->purge_url($url);

        // Check if both files are deleted (desktop and mobile)
        $this->assertFileDoesNotExist($cache_dir . md5($url) . '.html');
        $this->assertFileDoesNotExist($cache_dir . md5($url) . '|mobile' . '.html');

        // Cleanup
        @rmdir($cache_dir);
    }

    /**
     * Test purge_all method clears all cache
     */
    public function testPurgeAllClearsAllCache()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $cache_dir = '/tmp/cache/pages/';
        $meta_file = '/tmp/cache/meta.json';

        // Create cache directory and files
        @mkdir($cache_dir, 0755, true);
        file_put_contents($cache_dir . 'key1.html', 'test1');
        file_put_contents($cache_dir . 'key2.html', 'test2');
        file_put_contents($cache_dir . 'key3.html.gz', 'test3');
        file_put_contents($meta_file, json_encode(['key1' => [], 'key2' => []]));

        Functions\when('do_action')->returnArg();

        $cache = new Blitz_Cache_Cache();
        $cache->purge_all();

        // Check if all files are deleted
        $this->assertFileDoesNotExist($cache_dir . 'key1.html');
        $this->assertFileDoesNotExist($cache_dir . 'key2.html');
        $this->assertFileDoesNotExist($cache_dir . 'key3.html.gz');

        // Check if meta is reset
        $this->assertFileExists($meta_file);
        $meta = json_decode(file_get_contents($meta_file), true);
        $this->assertEmpty($meta);

        // Cleanup
        @unlink($meta_file);
        @rmdir($cache_dir);
    }

    /**
     * Test get_stats returns array with stats
     */
    public function testGetStatsReturnsStatsArray()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $stats_file = '/tmp/cache/stats.json';

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

        // Cleanup
        @unlink($stats_file);
    }

    /**
     * Test filter hook blitz_cache_should_cache works
     */
    public function testFilterHookBlitzCacheShouldCache()
    {
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'page_cache_enabled' => true
        ]);

        Functions\when('apply_filters')
            ->beCalledWithConsecutive(
                ['blitz_cache_should_cache', true],
                ['blitz_cache_should_cache', false]
            )
            ->returnValues([true, false]);

        $cache = new Blitz_Cache_Cache();

        // First call should return true (after filter)
        $this->assertTrue($cache->should_cache());

        // Mock the filter to return false
        global $wp_filter;
        $wp_filter = [];
        add_filter('blitz_cache_should_cache', function() { return false; });

        // Second call should return false (after filter)
        $this->assertFalse($cache->should_cache());
    }
}
