<?php
/**
 * PHPUnit Bootstrap File
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load Brain Monkey API FIRST - this sets up the patching framework
require_once __DIR__ . '/../vendor/brain/monkey/inc/api.php';

// Define minimal WordPress constants needed by the plugin
// Use cache directory path for tests to match BLITZ_CACHE_CACHE_DIR
// Use a fixed temp path for consistent testing across platforms
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', sys_get_temp_dir() . '/blitz-cache');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

// Define plugin constants before loading any classes
if (!defined('BLITZ_CACHE_VERSION')) {
    define('BLITZ_CACHE_VERSION', '1.0.0');
}
if (!defined('BLITZ_CACHE_PLUGIN_FILE')) {
    define('BLITZ_CACHE_PLUGIN_FILE', __DIR__ . '/../blitz-cache/blitz-cache.php');
}
if (!defined('BLITZ_CACHE_PLUGIN_DIR')) {
    define('BLITZ_CACHE_PLUGIN_DIR', __DIR__ . '/../blitz-cache/');
}
if (!defined('BLITZ_CACHE_PLUGIN_URL')) {
    define('BLITZ_CACHE_PLUGIN_URL', 'http://localhost/blitz-cache/');
}
if (!defined('BLITZ_CACHE_PLUGIN_BASENAME')) {
    define('BLITZ_CACHE_PLUGIN_BASENAME', 'blitz-cache/blitz-cache.php');
}
// Define cache directory with temp path for tests
// Use /cache/ subdirectory to match the plugin's expected structure
if (!defined('BLITZ_CACHE_CACHE_DIR')) {
    define('BLITZ_CACHE_CACHE_DIR', sys_get_temp_dir() . '/blitz-cache/cache/');
}
if (!defined('BLITZ_CACHE_MIN_WP')) {
    define('BLITZ_CACHE_MIN_WP', '6.0');
}
if (!defined('BLITZ_CACHE_MIN_PHP')) {
    define('BLITZ_CACHE_MIN_PHP', '8.0');
}

// Define minimal WordPress functions needed for plugin loading
if (!function_exists('add_action')) {
    function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1) {
        // Stub for plugin loading - tests will override with Brain Monkey
    }
}

// Note: do_action is NOT defined here to allow Brain Monkey to mock it in tests

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

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function) {
        // Stub for plugin loading
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function) {
        // Stub for plugin loading
    }
}

// Define WP_Error class if not exists
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $code;
        private $message;
        private $data;

        public function __construct($code = '', $message = '', $data = '') {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message($code = '') {
            return $this->message;
        }

        public function get_error_data($code = '') {
            return $this->data;
        }

        public function add($code, $message, $data = '') {
            // Stub
        }

        public function add_data($data, $code = '') {
            // Stub
        }
    }
}

// Define WP_Post class if not exists
if (!class_exists('WP_Post')) {
    class WP_Post {
        public $ID;
        public $post_author;
        public $post_date;
        public $post_date_gmt;
        public $post_content;
        public $post_title;
        public $post_excerpt;
        public $post_status;
        public $comment_status;
        public $ping_status;
        public $post_password;
        public $post_name;
        public $to_ping;
        public $pinged;
        public $post_modified;
        public $post_modified_gmt;
        public $post_content_filtered;
        public $post_parent;
        public $guid;
        public $menu_order;
        public $post_type;
        public $post_mime_type;
        public $comment_count;
        public $filter;

        public function __construct($post = null) {
            if (is_object($post)) {
                foreach (get_object_vars($post) as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }
}

// Define is_wp_error function
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof WP_Error;
    }
}

// Define transient functions
if (!function_exists('get_transient')) {
    function get_transient($transient) {
        return false; // Stub returns false (transient not found)
    }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        // Stub for setting transients
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        // Stub for deleting transients
        return true;
    }
}

// Load base test case - sets up Brain Monkey properly
require_once __DIR__ . '/TestCase.php';

// Load plugin classes manually
$plugin_dir = __DIR__ . '/../blitz-cache/';

// Manually load all required class files
// Note: Not loading the main plugin file as it has hooks that won't work in tests
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
require_once $plugin_dir . 'includes/class-blitz-cache-helpers.php';

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
