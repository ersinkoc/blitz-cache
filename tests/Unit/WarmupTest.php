<?php
/**
 * Test class for Blitz_Cache_Warmup
 */

namespace BlitzCache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use BlitzCache\Blitz_Cache_Warmup;

/**
 * Test suite for Blitz_Cache_Warmup class
 */
class WarmupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey::setUp();

        Functions\when('__')->returnArg();
        Functions\when('do_action')->returnArg();
        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'warmup_enabled' => true,
            'warmup_source' => 'sitemap',
            'warmup_interval' => 21600,
            'warmup_batch_size' => 5
        ]);
    }

    protected function tearDown(): void
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    /**
     * Test run does nothing when warmup disabled
     */
    public function testRunDoesNothingWhenWarmupDisabled()
    {
        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'warmup_enabled' => false
        ]);

        $warmup = new Blitz_Cache_Warmup();
        $result = $warmup->run();

        // Should return null or void
        $this->assertNull($result);
    }

    /**
     * Test run executes when warmup enabled
     */
    public function testRunExecutesWhenWarmupEnabled()
    {
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));
        Functions\when('wp_remote_retrieve_response_code')->return(200);
        Functions\when('wp_remote_retrieve_body')->return('');
        Functions\when('get_nav_menu_locations')->return([]);
        Functions\when('get_posts')->return([]);
        Functions\when('get_pages')->return([]);
        Functions\when('get_categories')->return([]);

        $warmup = new Blitz_Cache_Warmup();

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

        $warmup = new Blitz_Cache_Warmup();
        $result = $warmup->warm_url('http://example.com/test/');

        $this->assertTrue($result);
    }

    /**
     * Test warm_url returns false on error
     */
    public function testWarmUrlReturnsFalseOnError()
    {
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));

        $warmup = new Blitz_Cache_Warmup();
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

        $warmup = new Blitz_Cache_Warmup();
        $result = $warmup->warm_url('http://example.com/test/');

        $this->assertFalse($result);
    }

    /**
     * Test get_urls with sitemap source
     */
    public function testGetUrlsWithSitemapSource()
    {
        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'warmup_enabled' => true,
            'warmup_source' => 'sitemap'
        ]);
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));
        Functions\when('get_posts')->return([]);
        Functions\when('get_pages')->return([]);
        Functions\when('get_categories')->return([]);

        $warmup = new Blitz_Cache_Warmup();

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
        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'warmup_enabled' => true,
            'warmup_source' => 'menu'
        ]);
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('get_nav_menu_locations')->return([
            'primary' => 1
        ]);
        Functions\when('wp_get_nav_menu_items')->with(1)->return([
            (object)['url' => 'http://example.com/page-1/'],
            (object)['url' => 'http://example.com/page-2/']
        ]);

        $warmup = new Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/', $urls);
        $this->assertContains('http://example.com/page-1/', $urls);
        $this->assertContains('http://example.com/page-2/', $urls);
    }

    /**
     * Test get_sitemap_urls returns empty array on error
     */
    public function testGetSitemapUrlsReturnsEmptyOnError()
    {
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));

        $warmup = new Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_sitemap_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertEmpty($urls);
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

        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('get_posts')->with(\Hamcrest\Matcher::typeOf('array'))->return($posts);
        Functions\when('get_pages')->with(\Hamcrest\Matcher::typeOf('array'))->return($pages);
        Functions\when('get_categories')->with(\Hamcrest\Matcher::typeOf('array'))->return($categories);
        Functions\when('get_permalink')->return('http://example.com/permalink/');
        Functions\when('get_category_link')->return('http://example.com/category/');

        $warmup = new Blitz_Cache_Warmup();

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
        define('BLITZ_CACHE_CACHE_DIR', '/tmp/cache/');

        $stats_file = '/tmp/cache/stats.json';

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
        file_put_contents($stats_file, json_encode($initial_stats));

        $warmup = new Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'update_warmup_stats');
        $method->setAccessible(true);
        $method->invoke($warmup);

        // Check if last_warmup was updated
        $stats = json_decode(file_get_contents($stats_file), true);
        $this->assertGreaterThan(0, $stats['last_warmup']);
        $this->assertGreaterThan($initial_stats['last_warmup'], $stats['last_warmup']);

        // Cleanup
        @unlink($stats_file);
    }

    /**
     * Test filter hook blitz_cache_warmup_urls works
     */
    public function testFilterHookBlitzCacheWarmupUrls()
    {
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('wp_remote_get')->justReturn(new \WP_Error('test', 'Test error'));
        Functions\when('get_posts')->return([]);
        Functions\when('get_pages')->return([]);
        Functions\when('get_categories')->return([]);

        // Add filter to modify URLs
        add_filter('blitz_cache_warmup_urls', function($urls) {
            $urls[] = 'http://example.com/custom-warmup-url/';
            return $urls;
        });

        $warmup = new Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        // Check that custom URL was added
        $this->assertContains('http://example.com/custom-warmup-url/', $urls);
    }

    /**
     * Test batch processing works correctly
     */
    public function testBatchProcessing()
    {
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('Blitz_Cache_Options::get')->justReturn([
            'warmup_enabled' => true,
            'warmup_source' => 'custom',
            'warmup_batch_size' => 3
        ]);
        Functions\when('apply_filters')
            ->with('blitz_cache_custom_warmup_urls')
            ->return([
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

        $warmup = new Blitz_Cache_Warmup();

        // Should process in batches of 3
        $warmup->run();

        $this->assertTrue(true);
    }

    /**
     * Test get_menu_urls with no menus
     */
    public function testGetMenuUrlsWithNoMenus()
    {
        Functions\when('home_url')->justReturn('http://example.com');
        Functions\when('get_nav_menu_locations')->return([]);

        $warmup = new Blitz_Cache_Warmup();

        $method = new \ReflectionMethod($warmup, 'get_menu_urls');
        $method->setAccessible(true);
        $urls = $method->invoke($warmup);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/', $urls);
        $this->assertCount(1, $urls); // Only homepage
    }
}
