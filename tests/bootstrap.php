<?php
/**
 * PHPUnit Bootstrap File
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load Brain Monkey API FIRST - this sets up the patching framework
require_once __DIR__ . '/../vendor/brain/monkey/inc/api.php';

// Define minimal WordPress constants needed by the plugin
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/wordpress/wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

// Mock WordPress helper functions - only the ones needed for plugin loading
// Tests will mock other functions as needed
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://localhost/' . basename(dirname($file)) . '/';
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename($file);
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1) {
        // Mock action
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function) {
        // Mock activation hook
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function) {
        // Mock deactivation hook
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
        return update_option($option, $value);
    }
}

if (!function_exists('wp_remote_request')) {
    function wp_remote_request($url, $args = array()) {
        // Mock remote request
        return array('body' => '');
    }
}

// Load plugin classes manually to ensure they're available
$plugin_dir = __DIR__ . '/../blitz-cache/';
require_once $plugin_dir . 'includes/class-blitz-cache-activator.php';
require_once $plugin_dir . 'includes/class-blitz-cache-cache.php';
require_once $plugin_dir . 'includes/class-blitz-cache-cloudflare.php';
require_once $plugin_dir . 'includes/class-blitz-cache-deactivator.php';
require_once $plugin_dir . 'includes/class-blitz-cache-loader.php';
require_once $plugin_dir . 'includes/class-blitz-cache-minify.php';
require_once $plugin_dir . 'includes/class-blitz-cache-options.php';
require_once $plugin_dir . 'includes/class-blitz-cache-purge.php';
require_once $plugin_dir . 'includes/class-blitz-cache-updater.php';
require_once $plugin_dir . 'includes/class-blitz-cache-warmup.php';
require_once $plugin_dir . 'includes/class-blitz-cache.php';

// Load new utility classes
require_once $plugin_dir . 'includes/class-blitz-cache-exception.php';
require_once $plugin_dir . 'includes/class-blitz-cache-logger.php';
require_once $plugin_dir . 'includes/class-blitz-cache-error-handler.php';
require_once $plugin_dir . 'includes/class-blitz-cache-validator.php';
require_once $plugin_dir . 'includes/class-blitz-cache-helpers.php';

// Load main plugin file (this registers the autoloader and activation hooks)
require_once __DIR__ . '/../blitz-cache/blitz-cache.php';

// Set up test environment
define('BLITZ_CACHE_TEST_MODE', true);

// Mock $_SERVER variables
if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}

if (!isset($_SERVER['HTTPS'])) {
    $_SERVER['HTTPS'] = 'off';
}

if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
}

if (!isset($_COOKIE)) {
    $_COOKIE = array();
}

if (!isset($_POST)) {
    $_POST = array();
}

if (!isset($_GET)) {
    $_GET = array();
}

if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; PHPUnit)';
}
