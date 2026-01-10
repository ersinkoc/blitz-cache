<?php
/**
 * Base Test Case for BlitzCache Tests
 *
 * Provides common setup and helper methods for all tests.
 */

namespace BlitzCache\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

abstract class BlitzCacheTestCase extends TestCase
{
    protected string $test_cache_dir;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setup();

        // Set up test cache directory
        $this->test_cache_dir = sys_get_temp_dir() . '/blitz-cache-test/';

        // Define cache directory constant if not already defined
        if (!defined('BLITZ_CACHE_CACHE_DIR')) {
            define('BLITZ_CACHE_CACHE_DIR', $this->test_cache_dir);
        }

        // Create test cache directory
        if (!file_exists($this->test_cache_dir . 'pages/')) {
            @mkdir($this->test_cache_dir . 'pages/', 0755, true);
        }

        $this->setupCommonMocks();
    }

    protected function tearDown(): void
    {
        // Clean up test cache directory
        $this->cleanupTestCacheDir();

        // Reset Blitz_Cache_Options static properties
        $this->resetOptionsCache();

        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Set up common WordPress function mocks
     * Note: We don't mock functions that are already defined in bootstrap
     * (add_action, do_action, plugin_dir_path, etc.) since Brain Monkey
     * cannot redefine functions that are already defined.
     */
    protected function setupCommonMocks(): void
    {
        // Functions NOT defined in bootstrap - these are safe to mock
        Functions\when('__')->returnArg();
        Functions\when('esc_html__')->returnArg();
        Functions\when('esc_html_e')->returnArg();
        Functions\when('esc_attr__')->returnArg();
        Functions\when('esc_attr')->returnArg();
        Functions\when('_e')->returnArg();
        Functions\when('wp_json_encode')->returnArg();
        Functions\when('apply_filters')->returnArg();
        Functions\when('is_ssl')->justReturn(false);
        Functions\when('wp_is_mobile')->justReturn(false);
        Functions\when('is_user_logged_in')->justReturn(false);
        Functions\when('wp_schedule_single_event')->justReturn(true);
        Functions\when('sanitize_url')->returnArg();
        Functions\when('untrailingslashit')->returnArg();
        Functions\when('trailingslashit')->returnArg();
        Functions\when('admin_url')->justReturn('http://localhost/wp-admin/');
        Functions\when('home_url')->justReturn('http://localhost/');
        Functions\when('site_url')->justReturn('http://localhost/');
        Functions\when('add_filter')->justReturn(true);
        Functions\when('remove_action')->justReturn(true);
        Functions\when('remove_filter')->justReturn(true);
        Functions\when('wp_nonce_field')->justReturn('');
        Functions\when('wp_verify_nonce')->justReturn(true);
        Functions\when('wp_create_nonce')->justReturn('test_nonce');
        Functions\when('check_admin_referer')->justReturn(true);
        Functions\when('current_user_can')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);

        // Mock wp_remote_* functions
        Functions\when('wp_remote_get')->justReturn(['body' => '']);
        Functions\when('wp_remote_post')->justReturn(['body' => '']);
        Functions\when('wp_remote_request')->justReturn(['body' => '']);
        Functions\when('wp_remote_retrieve_body')->justReturn('');
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        // Note: is_wp_error is defined in bootstrap.php as a stub, cannot be mocked

        // Mock URL functions
        Functions\when('get_permalink')->justReturn('http://localhost/?p=1');
        Functions\when('get_post_type_archive_link')->justReturn('http://localhost/archive/');
        Functions\when('get_author_posts_url')->justReturn('http://localhost/author/1/');
        Functions\when('get_year_link')->justReturn('http://localhost/2024/');
        Functions\when('get_month_link')->justReturn('http://localhost/2024/01/');
        Functions\when('get_day_link')->justReturn('http://localhost/2024/01/01/');
        Functions\when('get_category_link')->justReturn('http://localhost/category/test/');
        Functions\when('get_tag_link')->justReturn('http://localhost/tag/test/');
        Functions\when('get_feed_link')->justReturn('http://localhost/feed/');

        // Mock get_option with default settings
        $this->mockDefaultOptions();
    }

    /**
     * Mock default plugin options
     */
    protected function mockDefaultOptions(): void
    {
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => true,
            'cache_logged_in' => false,
            'excluded_urls' => [],
            'excluded_cookies' => [],
            'mobile_cache' => false,
            'page_cache_ttl' => 86400,
            'gzip_enabled' => false,
            'html_minify_enabled' => false,
        ]);
    }

    /**
     * Mock specific option value
     */
    protected function mockOption(string $key, mixed $value): void
    {
        Functions\when('get_option')
            ->beCalledWith([$key])
            ->justReturn($value);
    }

    /**
     * Mock multiple options
     */
    protected function mockOptions(array $options): void
    {
        Functions\when('get_option')->justReturn($options);
    }

    /**
     * Reset Blitz_Cache_Options static cache
     */
    protected function resetOptionsCache(): void
    {
        if (class_exists('Blitz_Cache_Options')) {
            $reflection = new \ReflectionClass('Blitz_Cache_Options');

            // Reset settings property
            $settings = $reflection->getProperty('settings');
            $settings->setAccessible(true);
            $settings->setValue(null, null);

            // Reset cloudflare property
            $cloudflare = $reflection->getProperty('cloudflare');
            $cloudflare->setAccessible(true);
            $cloudflare->setValue(null, null);
        }
    }

    /**
     * Clean up test cache directory
     */
    protected function cleanupTestCacheDir(): void
    {
        if (is_dir($this->test_cache_dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->test_cache_dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    @rmdir($file->getPathname());
                } else {
                    @unlink($file->getPathname());
                }
            }
            @rmdir($this->test_cache_dir);
        }
    }

    /**
     * Create a test cache file
     */
    protected function createTestCacheFile(string $key, string $html): void
    {
        file_put_contents($this->test_cache_dir . 'pages/' . $key . '.html', $html);
    }

    /**
     * Create a test meta file
     */
    protected function createTestMetaFile(array $meta): void
    {
        file_put_contents($this->test_cache_dir . 'meta.json', json_encode($meta));
    }

    /**
     * Create a test stats file
     */
    protected function createTestStatsFile(array $stats): void
    {
        file_put_contents($this->test_cache_dir . 'stats.json', json_encode($stats));
    }
}
