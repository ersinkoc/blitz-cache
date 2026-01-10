<?php
/**
 * Plugin Name: Blitz Cache
 * Plugin URI: https://github.com/BlitzCache/blitzcache
 * Description: Zero-config WordPress caching with Cloudflare Edge integration.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Ersin KOÇ
 * Author URI: https://github.com/ersinkoc
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blitz-cache
 * Domain Path: /languages
 */

// Abort if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
if (!defined('BLITZ_CACHE_VERSION')) {
    define('BLITZ_CACHE_VERSION', '1.0.0');
}
if (!defined('BLITZ_CACHE_PLUGIN_FILE')) {
    define('BLITZ_CACHE_PLUGIN_FILE', __FILE__);
}
if (!defined('BLITZ_CACHE_PLUGIN_DIR')) {
    define('BLITZ_CACHE_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('BLITZ_CACHE_PLUGIN_URL')) {
    define('BLITZ_CACHE_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('BLITZ_CACHE_PLUGIN_BASENAME')) {
    define('BLITZ_CACHE_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
if (!defined('BLITZ_CACHE_CACHE_DIR')) {
    define('BLITZ_CACHE_CACHE_DIR', WP_CONTENT_DIR . '/cache/blitz-cache/');
}
if (!defined('BLITZ_CACHE_MIN_WP')) {
    define('BLITZ_CACHE_MIN_WP', '6.0');
}
if (!defined('BLITZ_CACHE_MIN_PHP')) {
    define('BLITZ_CACHE_MIN_PHP', '8.0');
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Blitz_Cache';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $file = BLITZ_CACHE_PLUGIN_DIR . 'includes/class-' .
            strtolower(str_replace('_', '-', $class)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['Blitz_Cache_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['Blitz_Cache_Deactivator', 'deactivate']);

// Initialize plugin
add_action('plugins_loaded', function() {
    $plugin = new Blitz_Cache();
    $plugin->run();
});

// Async stats update handler (for buffered hit/miss tracking)
add_action('blitz_cache_update_stats_async', function($args = []) {
    $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';

    try {
        if (!file_exists($stats_file)) {
            return;
        }

        $stats = json_decode(@file_get_contents($stats_file), true) ?: [];

        if (!empty($args['hits'])) {
            $stats['hits'] = ($stats['hits'] ?? 0) + (int) $args['hits'];
        }

        if (!empty($args['misses'])) {
            $stats['misses'] = ($stats['misses'] ?? 0) + (int) $args['misses'];
        }

        // Atomic write
        $temp_file = $stats_file . '.tmp.' . uniqid();
        @file_put_contents($temp_file, wp_json_encode($stats), LOCK_EX);
        @rename($temp_file, $stats_file);
        @chmod($stats_file, 0644);
    } catch (Exception $e) {
        // Silently fail to avoid breaking async operations
        if (function_exists('error_log')) {
            error_log('Blitz Cache async stats update failed: ' . $e->getMessage());
        }
    }
});
