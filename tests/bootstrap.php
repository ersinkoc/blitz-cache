<?php
/**
 * PHPUnit Bootstrap File
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load WordPress test suite if available
if (file_exists(__DIR__ . '/wordpress/wp-config.php')) {
    require_once __DIR__ . '/wordpress/wp-config.php';
    require_once __DIR__ . '/wordpress/wp-functions.php';
} else {
    // Mock WordPress functions
    if (!function_exists('__')) {
        function __($text, $domain = 'default') {
            return $text;
        }
    }

    if (!function_exists('esc_html__')) {
        function esc_html__($text, $domain = 'default') {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
    }

    if (!function_exists('esc_html_e')) {
        function esc_html_e($text, $domain = 'default') {
            echo esc_html__($text, $domain);
        }
    }

    if (!function_exists('esc_attr__')) {
        function esc_attr__($text, $domain = 'default') {
            return esc_attr($text);
        }
    }

    if (!function_exists('esc_attr')) {
        function esc_attr($text) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
    }

    if (!function_exists('esc_url__')) {
        function esc_url__($url, $protocols = null, $_context = 'display') {
            return esc_url($url, $protocols);
        }
    }

    if (!function_exists('esc_url')) {
        function esc_url($url, $protocols = null, $_context = 'display') {
            return filter_var($url, FILTER_SANITIZE_URL);
        }
    }

    if (!function_exists('esc_js')) {
        function esc_js($text) {
            return addslashes($text);
        }
    }

    if (!function_exists('wp_die')) {
        function wp_die($message, $title = '', $args = array()) {
            throw new Exception($message);
        }
    }

    if (!function_exists('wp_json_encode')) {
        function wp_json_encode($data, $options = 0, $depth = 512) {
            return json_encode($data, $options, $depth);
        }
    }

    if (!function_exists('add_action')) {
        function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1) {
            // Mock action
        }
    }

    if (!function_exists('add_filter')) {
        function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
            // Mock filter
        }
    }

    if (!function_exists('apply_filters')) {
        function apply_filters($tag, $value, ...$args) {
            return $value;
        }
    }

    if (!function_exists('do_action')) {
        function do_action($tag, ...$arg) {
            // Mock action
        }
    }

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
            // Mock activation hook
        }
    }

    if (!function_exists('register_deactivation_hook')) {
        function register_deactivation_hook($file, $function) {
            // Mock deactivation hook
        }
    }

    if (!function_exists('deactivate_plugins')) {
        function deactivate_plugins($plugin, $silent = false, $network_wide = null) {
            // Mock deactivate
        }
    }

    if (!function_exists('add_option')) {
        function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
            return update_option($option, $value);
        }
    }

    if (!function_exists('update_option')) {
        function update_option($option, $value, $autoload = null) {
            return true;
        }
    }

    if (!function_exists('get_option')) {
        function get_option($option, $default = false) {
            return $default;
        }
    }

    if (!function_exists('delete_option')) {
        function delete_option($option) {
            return true;
        }
    }

    if (!function_exists('add_menu_page')) {
        function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null) {
            return $menu_slug;
        }
    }

    if (!function_exists('wp_enqueue_style')) {
        function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
            // Mock enqueue
        }
    }

    if (!function_exists('wp_enqueue_script')) {
        function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
            // Mock enqueue
        }
    }

    if (!function_exists('wp_localize_script')) {
        function wp_localize_script($handle, $object_name, $l10n) {
            // Mock localize
        }
    }

    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) {
            return trim(strip_tags($str));
        }
    }

    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) {
            return strtolower(preg_replace('/[^a-z0-9_\-]/', '', $key));
        }
    }

    if (!function_exists('sanitize_textarea_field')) {
        function sanitize_textarea_field($str) {
            return sanitize_text_field($str);
        }
    }

    if (!function_exists('sanitize_url')) {
        function sanitize_url($url, $_protocols = null) {
            return esc_url_raw($url, $_protocols);
        }
    }

    if (!function_exists('esc_url_raw')) {
        function esc_url_raw($url, $_protocols = null) {
            return filter_var($url, FILTER_SANITIZE_URL);
        }
    }

    if (!function_exists('absint')) {
        function absint($maybeint) {
            return abs(intval($maybeint));
        }
    }

    if (!function_exists('wp_create_nonce')) {
        function wp_create_nonce($action = -1) {
            return 'nonce_' . $action . '_123456';
        }
    }

    if (!function_exists('check_ajax_referer')) {
        function check_ajax_referer($nonce, $query_arg = false, $die = true) {
            return true;
        }
    }

    if (!function_exists('current_user_can')) {
        function current_user_can($capability) {
            return true;
        }
    }

    if (!function_exists('is_admin')) {
        function is_admin() {
            return true;
        }
    }

    if (!function_exists('is_ssl')) {
        function is_ssl() {
            return false;
        }
    }

    if (!function_exists('wp_is_mobile')) {
        function wp_is_mobile() {
            return false;
        }
    }

    if (!function_exists('is_user_logged_in')) {
        function is_user_logged_in() {
            return false;
        }
    }

    if (!function_exists('fnmatch')) {
        function fnmatch($pattern, $string) {
            return preg_match('#^' . strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.')) . '$#i', $string);
        }
    }

    if (!function_exists('wp_salt')) {
        function wp_salt($scheme = 'auth') {
            return 'salt_' . $scheme . '_1234567890abcdef';
        }
    }

    if (!function_exists('file_get_contents')) {
        function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null) {
            if ($maxlen !== null) {
                return substr(file_get_contents($filename, $use_include_path, $context), $offset, $maxlen);
            }
            return file_get_contents($filename, $use_include_path, $context);
        }
    }

    if (!function_exists('file_put_contents')) {
        function file_put_contents($filename, $data, $flags = 0, $context = null) {
            return file_put_contents($filename, $data, $flags);
        }
    }

    if (!function_exists('file_exists')) {
        function file_exists($filename) {
            return file_exists($filename);
        }
    }

    if (!function_exists('is_writable')) {
        function is_writable($filename) {
            return is_writable($filename);
        }
    }

    if (!function_exists('rename')) {
        function rename($oldname, $newname, $context = null) {
            return rename($oldname, $newname);
        }
    }

    if (!function_exists('copy')) {
        function copy($source, $dest, $context = null) {
            return copy($source, $dest);
        }
    }

    if (!function_exists('unlink')) {
        function unlink($filename, $context = null) {
            return unlink($filename);
        }
    }

    if (!function_exists('mkdir')) {
        function mkdir($pathname, $mode = 0777, $recursive = false, $context = null) {
            return mkdir($pathname, $mode, $recursive);
        }
    }

    if (!function_exists('wp_mkdir_p')) {
        function wp_mkdir_p($target) {
            return @mkdir($target, 0755, true);
        }
    }

    if (!function_exists('glob')) {
        function glob($pattern, $flags = 0) {
            return glob($pattern, $flags);
        }
    }

    if (!function_exists('scandir')) {
        function scandir($directory, $sorting_order = SCANDIR_SORT_ASCENDING) {
            return scandir($directory, $sorting_order);
        }
        define('SCANDIR_SORT_ASCENDING', 0);
    }

    if (!function_exists('rmdir')) {
        function rmdir($dirname, $context = null) {
            return rmdir($dirname);
        }
    }

    if (!function_exists('filesize')) {
        function filesize($filename) {
            return filesize($filename);
        }
    }

    if (!function_exists('readfile')) {
        function readfile($filename, $use_include_path = false, $context = null) {
            return readfile($filename, $use_include_path, $context);
        }
    }

    if (!function_exists('gzencode')) {
        function gzencode($data, $level = -1) {
            return gzencode($data, $level);
        }
    }

    if (!function_exists('json_decode')) {
        function json_decode($json, $assoc = false, $depth = 512, $flags = 0) {
            return json_decode($json, $assoc, $depth, $flags);
        }
    }

    if (!function_exists('json_encode')) {
        function json_encode($value, $options = 0, $depth = 512) {
            return json_encode($value, $options, $depth);
        }
    }

    if (!function_exists('base64_encode')) {
        function base64_encode($str) {
            return base64_encode($str);
        }
    }

    if (!function_exists('base64_decode')) {
        function base64_decode($data, $strict = false) {
            return base64_decode($data, $strict);
        }
    }

    if (!function_exists('openssl_encrypt')) {
        function openssl_encrypt($data, $method, $password, $options = 0, $iv = "", $tag = null, $tag_length = 16) {
            // Fallback to base64
            return base64_encode($data);
        }
    }

    if (!function_exists('openssl_decrypt')) {
        function openssl_decrypt($data, $method, $password, $options = 0, $iv = "", $tag = "", $tag_length = 16) {
            // Fallback to base64
            return base64_decode($data);
        }
    }

    if (!function_exists('openssl_random_pseudo_bytes')) {
        function openssl_random_pseudo_bytes($length, &$crypto_strong = null) {
            $crypto_strong = true;
            return str_repeat('0', $length);
        }
    }

    if (!function_exists('substr')) {
        function substr($string, $start, $length = 2147483647) {
            return substr($string, $start, $length);
        }
    }
}

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
