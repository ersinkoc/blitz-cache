<?php
/**
 * Blitz Cache - Advanced Cache Dropin
 *
 * This file is copied to wp-content/advanced-cache.php during activation.
 * It intercepts requests before WordPress fully loads.
 *
 * @package Blitz_Cache
 * @version 1.0.0
 */

// Identifier for Blitz Cache
define('BLITZ_CACHE_DROPIN', true);

// Abort if WP_CACHE is not enabled
if (!defined('WP_CACHE') || !WP_CACHE) {
    return;
}

// Get cache directory
$cache_dir = defined('WP_CONTENT_DIR')
    ? WP_CONTENT_DIR . '/cache/blitz-cache/'
    : dirname(__DIR__) . '/cache/blitz-cache/';

// Check if plugin is properly installed
if (!file_exists($cache_dir . 'meta.json')) {
    return;
}

// Get settings
$settings_option = 'blitz_cache_settings';
$settings = [];

// Try to read from object cache first (if available)
if (function_exists('wp_cache_get')) {
    $settings = wp_cache_get($settings_option, 'options');
}

// Fallback to direct DB read
if (empty($settings)) {
    // We can't use WordPress functions yet, so read directly
    // This is only used for the initial check - full settings loaded later
    $settings = [
        'page_cache_enabled' => true,
        'cache_logged_in' => false,
    ];
}

// Quick checks before attempting to serve cache
if (empty($settings['page_cache_enabled'])) {
    return;
}

// Don't cache CLI requests
if (php_sapi_name() === 'cli') {
    return;
}

// Only GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    return;
}

// Check for WordPress logged-in cookies
if (!$settings['cache_logged_in']) {
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'wordpress_logged_in_') === 0) {
            return;
        }
    }
}

// Check for WooCommerce cart cookies
$excluded_cookies = ['woocommerce_cart_hash', 'woocommerce_items_in_cart'];
foreach ($excluded_cookies as $cookie) {
    if (isset($_COOKIE[$cookie]) && $_COOKIE[$cookie]) {
        return;
    }
}

// Build cache key
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$cache_key = md5($url);

// Check for cached file
$cache_file = $cache_dir . 'pages/' . $cache_key . '.html';
$cache_file_gz = $cache_file . '.gz';

// Meta file with static caching within request
$meta_file = $cache_dir . 'meta.json';
static $blitz_meta_cache = null;
static $blitz_meta_cache_time = 0;

// Check if we need to refresh the meta cache
$meta_file_time = file_exists($meta_file) ? filemtime($meta_file) : 0;
if ($blitz_meta_cache === null || $blitz_meta_cache_time < $meta_file_time) {
    if (file_exists($meta_file)) {
        $meta_json = @file_get_contents($meta_file);
        if ($meta_json !== false) {
            $blitz_meta_cache = json_decode($meta_json, true);
            $blitz_meta_cache_time = $meta_file_time;
        }
    }
}

// Try to serve gzipped version
if (file_exists($cache_file_gz) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
    if ($blitz_meta_cache && isset($blitz_meta_cache[$cache_key]) && time() < $blitz_meta_cache[$cache_key]['expires']) {
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Encoding: gzip');
        header('X-Blitz-Cache: HIT (gzip, dropin)');
        header('Vary: Accept-Encoding');
        @readfile($cache_file_gz);
        exit;
    }
}

// Try regular HTML
if (file_exists($cache_file)) {
    if ($blitz_meta_cache && isset($blitz_meta_cache[$cache_key]) && time() < $blitz_meta_cache[$cache_key]['expires']) {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Blitz-Cache: HIT (dropin)');
        @readfile($cache_file);
        exit;
    }
}

// No cache hit - continue loading WordPress
// The plugin will handle caching the response
