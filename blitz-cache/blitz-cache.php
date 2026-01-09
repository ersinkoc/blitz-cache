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
define('BLITZ_CACHE_VERSION', '1.0.0');
define('BLITZ_CACHE_PLUGIN_FILE', __FILE__);
define('BLITZ_CACHE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLITZ_CACHE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BLITZ_CACHE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BLITZ_CACHE_CACHE_DIR', WP_CONTENT_DIR . '/cache/blitz-cache/');
define('BLITZ_CACHE_MIN_WP', '6.0');
define('BLITZ_CACHE_MIN_PHP', '8.0');

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
