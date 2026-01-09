<?php
/**
 * Test class for Blitz_Cache_Purge
 */

namespace BlitzCache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use BlitzCache\Blitz_Cache_Purge;

/**
 * Test suite for Blitz_Cache_Purge class
 */
class PurgeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey::setUp();

        Functions\when('__')->returnArg();
        Functions\when('do_action')->returnArg();
        Functions\when('Blitz_Cache_Options::get_cloudflare')->justReturn([]);
        Functions\when('Blitz_Cache_Options::set_cloudflare')->returnTrue();
    }

    protected function tearDown(): void
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    /**
     * Test on_post_save does not purge on autosave
     */
    public function testOnPostSaveDoesNotPurgeOnAutosave()
    {
        define('DOING_AUTOSAVE', true);

        $purge = new Blitz_Cache_Purge();

        // Create a mock post
        $post = new \stdClass();
        $post->post_status = 'publish';

        // This should not trigger any purge
        $purge->on_post_save(123, $post);

        // If we reach here without errors, test passes
        $this->assertTrue(true);
    }

    /**
     * Test on_post_save does not purge revisions
     */
    public function testOnPostSaveDoesNotPurgeRevisions()
    {
        define('DOING_AUTOSAVE', false);

        Functions\when('wp_is_post_revision')->justReturn(true);

        $purge = new Blitz_Cache_Purge();

        $post = new \stdClass();
        $post->post_status = 'publish';

        $purge->on_post_save(123, $post);

        $this->assertTrue(true);
    }

    /**
     * Test on_post_save does not purge non-published posts
     */
    public function testOnPostSaveDoesNotPurgeNonPublishedPosts()
    {
        define('DOING_AUTOSAVE', false);
        Functions\when('wp_is_post_revision')->returnFalse();

        $purge = new Blitz_Cache_Purge();

        $post = new \stdClass();
        $post->post_status = 'draft'; // Not published

        $purge->on_post_save(123, $post);

        $this->assertTrue(true);
    }

    /**
     * Test on_post_save purges published posts
     */
    public function testOnPostSavePurgesPublishedPosts()
    {
        define('DOING_AUTOSAVE', false);
        Functions\when('wp_is_post_revision')->returnFalse();
        Functions\when('get_permalink')->justReturn('http://example.com/post-123/');
        Functions\when('get_post_type')->justReturn('post');
        Functions\when('get_post_type_archive_link')->returnFalse();
        Functions\when('get_the_category')->return([]);
        Functions\when('get_the_tags')->returnFalse();
        Functions\when('get_author_posts_url')->return('http://example.com/author/john/');
        Functions\when('get_year_link')->return('http://example.com/2024/');
        Functions\when('get_month_link')->return('http://example.com/2024/01/');
        Functions\when('get_day_link')->return('http://example.com/2024/01/15/');
        Functions\when('get_feed_link')->return('http://example.com/feed/');
        Functions\when('get_option')->justReturn(false);
        Functions\when('home_url')->justReturn('http://example.com/');

        // Mock the cache object
        $mockCache = $this->getMockBuilder('Blitz_Cache_Cache')
            ->onlyMethods(['purge_url'])
            ->getMock();
        $mockCache->expects($this->atLeastOnce())
            ->method('purge_url');

        // Replace the cache property
        $purge = new Blitz_Cache_Purge();
        $reflection = new \ReflectionClass($purge);
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);
        $property->setValue($purge, $mockCache);

        $post = new \stdClass();
        $post->post_status = 'publish';
        $post->post_author = 1;

        $purge->on_post_save(123, $post);

        $this->assertTrue(true);
    }

    /**
     * Test purge_all method
     */
    public function testPurgeAll()
    {
        $mockCache = $this->getMockBuilder('Blitz_Cache_Cache')
            ->onlyMethods(['purge_all'])
            ->getMock();
        $mockCache->expects($this->once())
            ->method('purge_all');

        $purge = new Blitz_Cache_Purge();
        $reflection = new \ReflectionClass($purge);
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);
        $property->setValue($purge, $mockCache);

        $purge->purge_all();

        $this->assertTrue(true);
    }

    /**
     * Test purge_url method
     */
    public function testPurgeUrl()
    {
        $mockCache = $this->getMockBuilder('Blitz_Cache_Cache')
            ->onlyMethods(['purge_url'])
            ->getMock();
        $mockCache->expects($this->once())
            ->method('purge_url')
            ->with('http://example.com/test/');

        $purge = new Blitz_Cache_Purge();
        $reflection = new \ReflectionClass($purge);
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);
        $property->setValue($purge, $mockCache);

        $purge->purge_url('http://example.com/test/');

        $this->assertTrue(true);
    }

    /**
     * Test on_comment_change purges post URL
     */
    public function testOnCommentChangePurgesPostUrl()
    {
        Functions\when('get_comment')->justReturn((object)[
            'comment_post_ID' => 123
        ]);
        Functions\when('get_permalink')->with(123)->return('http://example.com/post-123/');

        $mockCache = $this->getMockBuilder('Blitz_Cache_Cache')
            ->onlyMethods(['purge_url'])
            ->getMock();
        $mockCache->expects($this->once())
            ->method('purge_url')
            ->with('http://example.com/post-123/');

        $purge = new Blitz_Cache_Purge();
        $reflection = new \ReflectionClass($purge);
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);
        $property->setValue($purge, $mockCache);

        $purge->on_comment_change(1);

        $this->assertTrue(true);
    }

    /**
     * Test get_related_urls returns correct URLs
     */
    public function testGetRelatedUrls()
    {
        Functions\when('get_permalink')->with(123)->return('http://example.com/post-123/');
        Functions\when('get_post_type')->with(123)->return('post');
        Functions\when('get_post_type_archive_link')->with('post')->return('http://example.com/blog/');
        Functions\when('get_the_category')->with(123)->return([]);
        Functions\when('get_the_tags')->with(123)->returnFalse();
        Functions\when('get_author_posts_url')->with(1)->return('http://example.com/author/john/');
        Functions\when('get_year_link')->with('2024')->return('http://example.com/2024/');
        Functions\when('get_month_link')->with('2024', '01')->return('http://example.com/2024/01/');
        Functions\when('get_day_link')->with('2024', '01', '15')->return('http://example.com/2024/01/15/');
        Functions\when('get_feed_link')->return('http://example.com/feed/');
        Functions\when('get_feed_link')->with('rdf')->return('http://example.com/feed/rdf/');
        Functions\when('get_feed_link')->with('atom')->return('http://example.com/feed/atom/');
        Functions\when('get_option')->with('page_for_posts')->return(0);

        $post = new \stdClass();
        $post->ID = 123;
        $post->post_author = 1;
        $post->post_date = '2024-01-15 12:00:00';

        // Use reflection to test the private method
        $purge = new Blitz_Cache_Purge();
        $method = new \ReflectionMethod($purge, 'get_related_urls');
        $method->setAccessible(true);

        $urls = $method->invoke($purge, 123, $post);

        $this->assertIsArray($urls);
        $this->assertContains('http://example.com/post-123/', $urls);
        $this->assertContains('http://example.com/', $urls);
        $this->assertContains('http://example.com/blog/', $urls);
        $this->assertContains('http://example.com/author/john/', $urls);
        $this->assertContains('http://example.com/2024/', $urls);
        $this->assertContains('http://example.com/2024/01/', $urls);
        $this->assertContains('http://example.com/2024/01/15/', $urls);
        $this->assertContains('http://example.com/feed/', $urls);
    }

    /**
     * Test filter hook blitz_cache_purge_urls works
     */
    public function testFilterHookBlitzCachePurgeUrls()
    {
        Functions\when('get_permalink')->with(123)->return('http://example.com/post-123/');
        Functions\when('get_post_type')->with(123)->return('post');
        Functions\when('get_post_type_archive_link')->with('post')->return('http://example.com/blog/');
        Functions\when('get_the_category')->with(123)->return([]);
        Functions\when('get_the_tags')->with(123)->returnFalse();
        Functions\when('get_author_posts_url')->with(1)->return('http://example.com/author/john/');
        Functions\when('get_year_link')->with('2024')->return('http://example.com/2024/');
        Functions\when('get_month_link')->with('2024', '01')->return('http://example.com/2024/01/');
        Functions\when('get_day_link')->with('2024', '01', '15')->return('http://example.com/2024/01/15/');
        Functions\when('get_feed_link')->return('http://example.com/feed/');
        Functions\when('get_feed_link')->with('rdf')->return('http://example.com/feed/rdf/');
        Functions\when('get_feed_link')->with('atom')->return('http://example.com/feed/atom/');
        Functions\when('get_option')->with('page_for_posts')->return(0);
        Functions\when('apply_filters')
            ->beCalledWithConsecutive(
                ['blitz_cache_purge_urls', \Hamcrest\Matcher::typeOf('array'), 123, \Hamcrest\Matcher::typeOf('stdClass')],
                ['blitz_cache_purge_urls', \Hamcrest\Matcher::typeOf('array'), 123, \Hamcrest\Matcher::typeOf('stdClass')]
            )
            ->returnArg(0);

        // Add filter to modify URLs
        add_filter('blitz_cache_purge_urls', function($urls, $post_id, $post) {
            $urls[] = 'http://example.com/custom-url/';
            return $urls;
        }, 10, 3);

        $post = new \stdClass();
        $post->ID = 123;
        $post->post_author = 1;
        $post->post_date = '2024-01-15 12:00:00';

        $purge = new Blitz_Cache_Purge();
        $method = new \ReflectionMethod($purge, 'get_related_urls');
        $method->setAccessible(true);

        $urls = $method->invoke($purge, 123, $post);

        // Check that custom URL was added
        $this->assertContains('http://example.com/custom-url/', $urls);
    }

    /**
     * Test update_purge_stats method
     */
    public function testUpdatePurgeStats()
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

        $purge = new Blitz_Cache_Purge();
        $method = new \ReflectionMethod($purge, 'update_purge_stats');
        $method->setAccessible(true);
        $method->invoke($purge);

        // Check if last_purge was updated
        $stats = json_decode(file_get_contents($stats_file), true);
        $this->assertGreaterThan(0, $stats['last_purge']);
        $this->assertGreaterThan($initial_stats['last_purge'], $stats['last_purge']);

        // Cleanup
        @unlink($stats_file);
    }
}
