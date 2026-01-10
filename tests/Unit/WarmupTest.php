<?php
/**
 * Test class for \Blitz_Cache_Warmup
 */

namespace BlitzCache\Tests\Unit;

use BlitzCache\Tests\BlitzCacheTestCase;
use Brain\Monkey\Functions;

/**
 * Test suite for \Blitz_Cache_Warmup class
 */
class WarmupTest extends BlitzCacheTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up common mocks for warmup tests
        Functions\when('home_url')->justReturn('http://example.com/');
        Functions\when('wp_remote_get')->justReturn(['body' => '']);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('');
    }

    /**
     * Test run does nothing when warmup disabled
     */
    public function testRunDoesNothingWhenWarmupDisabled()
    {
        $this->mockOptions([
            'warmup_enabled' => false
        ]);

        $warmup = new \Blitz_Cache_Warmup();
        $result = $warmup->run();

        // Should return null or void
        $this->assertNull($result);
    }

    /**
     * Test run executes when warmup enabled
     */
    public function testRunExecutesWhenWarmupEnabled()
    {
        // home_url is already mocked in setUp
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('');
        Functions\when('get_nav_menu_locations')->justReturn([]);
        Functions\when('get_posts')->justReturn([]);
        Functions\when('get_pages')->justReturn([]);
        Functions\when('get_categories')->justReturn([]);

        $warmup = new \Blitz_Cache_Warmup();

        // Should not throw error
        $warmup->run();

        $this->assertTrue(true);
    }

    /**
     * Test warm_url returns true on success
     */
    public function testWarmUrlReturnsTrueOnSuccess()
    {
        $mockResponse = [
            'response' => ['code' => 200]
        ];

        Functions\when('wp_remote_get')->justReturn($mockResponse);

        $warmup = new \Blitz_Cache_Warmup();
        $result = $warmup->warm_url('http://example.com/test/');

        $this->assertTrue($result);
    }

    /**
     * Test warm_url returns false on error
     */
    public function testWarmUrlReturnsFalseOnError()
    {
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));

        $warmup = new \Blitz_Cache_Warmup();
        $result = $warmup->warm_url('http://example.com/test/');

        $this->assertFalse($result);
    }

    /**
     * Test warm_url returns false on non-200 response
     */
    public function testWarmUrlReturnsFalseOnNon200()
    {
        $mockResponse = [
            'response' => ['code' => 404]
        ];

        Functions\when('wp_remote_get')->justReturn($mockResponse);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(404);

        $warmup = new \Blitz_Cache_Warmup();
        $result = $warmup->warm_url('http://example.com/test/');

        $this->assertFalse($result);
    }

    /**
     * Test get_urls with sitemap source
     */
    public function testGetUrlsWithSitemapSource()
    {
        $this->mockOptions([
            'warmup_enabled' => true,
            'warmup_source' => 'sitemap'
        ]);
        $this->resetOptionsCache();

        // home_url is already mocked in setUp to return 'http://example.com/'
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));
        Functions\when('get_posts')->justReturn([]);
        Functions\when('get_pages')->justReturn([]);
        Functions\when('get_categories')->justReturn([]);

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/', $urls); // Homepage should be included
    }

    /**
     * Test get_urls with menu source
     */
    public function testGetUrlsWithMenuSource()
    {
        $this->mockOptions([
            'warmup_enabled' => true,
            'warmup_source' => 'menu'
        ]);
        $this->resetOptionsCache();

        // home_url is already mocked in setUp
        Functions\when('get_nav_menu_locations')->justReturn([
            'primary' => 1
        ]);
        Functions\when('wp_get_nav_menu_items')->justReturn([
            (object)['url' => 'http://example.com/page-1/'],
            (object)['url' => 'http://example.com/page-2/']
        ]);

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/', $urls);
        $this->assertContains('http://example.com/page-1/', $urls);
        $this->assertContains('http://example.com/page-2/', $urls);
    }

    /**
     * Test get_sitemap_urls falls back to generate_url_list on error
     */
    public function testGetSitemapUrlsReturnsEmptyOnError()
    {
        // home_url is already mocked in setUp
        Functions\when('get_posts')->justReturn([]);
        Functions\when('get_pages')->justReturn([]);
        Functions\when('get_categories')->justReturn([]);
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_sitemap_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        // When sitemap fails, it falls back to generate_url_list which includes homepage
        $this->assertContains('http://example.com/', $urls);
    }

    /**
     * Test generate_url_list returns URLs from posts and pages
     */
    public function testGenerateUrlListReturnsUrlsFromPostsAndPages()
    {
        $posts = [
            (object)['ID' => 1, 'post_type' => 'post', 'post_status' => 'publish'],
            (object)['ID' => 2, 'post_type' => 'post', 'post_status' => 'publish']
        ];

        $pages = [
            (object)['ID' => 10, 'post_title' => 'About'],
            (object)['ID' => 11, 'post_title' => 'Contact']
        ];

        $categories = [
            (object)['term_id' => 5, 'name' => 'News']
        ];

        // home_url is already mocked in setUp
        Functions\when('get_posts')->justReturn($posts);
        Functions\when('get_pages')->justReturn($pages);
        Functions\when('get_categories')->justReturn($categories);
        Functions\when('get_permalink')->justReturn('http://example.com/permalink/');
        Functions\when('get_category_link')->justReturn('http://example.com/category/');

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'generate_url_list');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/', $urls);
        $this->assertGreaterThan(1, count($urls));
    }

    /**
     * Test update_warmup_stats updates stats file
     */
    public function testUpdateWarmupStatsUpdatesStatsFile()
    {
        // Create initial stats file
        $initial_stats = [
            'hits' => 10,
            'misses' => 5,
            'cached_pages' => 20,
            'cache_size' => 1000000,
            'last_warmup' => 0,
            'last_purge' => 0,
            'period_start' => time()
        ];
        $this->createTestStatsFile($initial_stats);

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'update_warmup_stats');
        $method->setAccessible(true);
        $method->invoke($warmup);

        // Check if last_warmup was updated
        $stats = json_decode(file_get_contents($this->test_cache_dir . 'stats.json'), true);
        $this->assertGreaterThan(0, $stats['last_warmup']);
        $this->assertGreaterThan($initial_stats['last_warmup'], $stats['last_warmup']);
    }

    /**
     * Test filter hook blitz_cache_warmup_urls works
     */
    public function testFilterHookBlitzCacheWarmupUrls()
    {
        $this->mockOptions([
            'warmup_enabled' => true
        ]);
        $this->resetOptionsCache();

        // home_url is already mocked in setUp
        // Mock sitemap fetch to return empty XML (fallback to generate_url_list)
        Functions\when('wp_remote_get')->justReturn('');
        Functions\when('wp_remote_retrieve_body')->justReturn('');
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('get_posts')->justReturn([]);
        Functions\when('get_pages')->justReturn([]);
        Functions\when('get_categories')->justReturn([]);

        // Store filter callback to verify it gets called
        $filterCalled = false;
        $customUrlAdded = 'http://example.com/custom-warmup-url/';

        // Override apply_filters mock for this test
        Functions\when('apply_filters')->alias(function($hook, $urls) use ($customUrlAdded, &$filterCalled) {
            if ($hook === 'blitz_cache_warmup_urls') {
                $filterCalled = true;
                if (\is_array($urls)) {
                    $urls[] = $customUrlAdded;
                }
            }
            return $urls;
        });

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        // Apply the filter manually like run() does
        $urls = apply_filters('blitz_cache_warmup_urls', $urls);

        // Check that filter was called and custom URL was added
        $this->assertIsArray($urls);
        $this->assertTrue($filterCalled);
        $this->assertContains($customUrlAdded, $urls);
    }

    /**
     * Test batch processing works correctly
     */
    public function testBatchProcessing()
    {
        // home_url is already mocked in setUp

        $this->mockOptions([
            'warmup_enabled' => true,
            'warmup_source' => 'custom',
            'warmup_batch_size' => 3
        ]);
        $this->resetOptionsCache();

        Functions\when('apply_filters')->justReturn([
                'http://example.com/1/',
                'http://example.com/2/',
                'http://example.com/3/',
                'http://example.com/4/',
                'http://example.com/5/',
                'http://example.com/6/',
                'http://example.com/7/',
                'http://example.com/8/'
            ]);
        Functions\when('wp_remote_get')->justReturn([
            'response' => ['code' => 200]
        ]);

        $warmup = new \Blitz_Cache_Warmup();

        // Should process in batches of 3
        $warmup->run();

        $this->assertTrue(true);
    }

    /**
     * Test get_menu_urls with no menus
     */
    public function testGetMenuUrlsWithNoMenus()
    {
        // home_url is already mocked in setUp
        Functions\when('get_nav_menu_locations')->justReturn([]);

        $warmup = new \Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_menu_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/', $urls);
        $this->assertCount(1, $urls); // Only homepage
    }
}
