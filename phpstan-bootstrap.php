<?php
/**
 * PHPStan Bootstrap File
 *
 * This file provides type declarations for WordPress functions
 * that PHPStan needs to understand for static analysis.
 */

/**
 * WordPress Core Functions
 */

// Options API
function get_option(string $option, mixed $default = false): mixed { return $default; }
function update_option(string $option, mixed $value, mixed $autoload = null): bool { return true; }
function add_option(string $option, mixed $value, string $deprecated = '', string $autoload = 'yes'): bool { return true; }
function delete_option(string $option): bool { return true; }
function get_site_option(string $option, mixed $default = false, bool $deprecated = true): mixed { return $default; }
function update_site_option(string $option, mixed $value): bool { return true; }
function add_site_option(string $option, mixed $value): bool { return true; }
function delete_site_option(string $option): bool { return true; }

// User API
function get_userdata(int $user_id): ?WP_User { return null; }
function get_user_by(string $field, int $value): ?WP_User { return null; }
function current_user_can(string $capability, mixed ...$args): bool { return true; }
function user_can(int $user_id, string $capability): bool { return true; }
function is_user_logged_in(): bool { return false; }
function wp_get_current_user(): WP_User { return new WP_User(); }
function wp_set_current_user(int $user_id, string $login = ''): WP_User { return new WP_User(); }

// Post API
function get_post(int|WP_Post $post = null, string $output = OBJECT, string $filter = 'raw'): ?WP_Post { return null; }
function get_post_field(string $field, int|WP_Post $post, string $context = 'display'): mixed { return null; }
function get_post_status(int|WP_Post $post = null): string|false { return false; }
function get_post_type(int|WP_Post $post = null): string|false { return false; }
function get_post_type_object(string $post_type): ?WP_Post_Type { return null; }
function get_post_type_archive_link(string $post_type): string|false { return false; }
function get_permalink(int|WP_Post $post = 0, bool $leavename = false): string|false { return false; }
function get_the_permalink(int|WP_Post $post = 0): string|false { return false; }
function wp_insert_post(array $args, bool $wp_error = false): int|WP_Error { return new WP_Error(); }
function wp_update_post(array $args, bool $wp_error = false): int|WP_Error { return new WP_Error(); }
function wp_delete_post(int $post_id, bool $force_delete = false): WP_Post|false|null { return null; }
function wp_publish_post(int $post_id): bool|WP_Error { return true; }
function is_post(int|WP_Post $post = null): bool { return false; }
function is_page(int|WP_Post $page = null): bool { return false; }
function is_singular(int|string|array $post_types = null): bool { return false; }

// Taxonomy API
function get_the_category(int $post_id = 0): array { return []; }
function get_the_tags(int $post_id = 0): array|false { return false; }
function get_term(int|WP_Term|object $term, string $taxonomy = '', string $output = OBJECT, string $filter = 'raw'): ?WP_Term { return null; }
function get_term_by(string $field, int|string $value, string $taxonomy = '', string $output = OBJECT, string $filter = 'raw'): ?WP_Term { return null; }
function get_category_link(int $category_id): string|false { return false; }
function get_tag_link(int $tag_id): string|false { return false; }
function get_term_link(int|WP_Term $term, string $taxonomy = ''): string|WP_Error { return new WP_Error(); }
function get_categories(array|string $args = ''): array { return []; }
function get_tags(array|string $args = ''): array { return []; }
function get_the_terms(int|WP_Post $post, string $taxonomy): array|false { return false; }
function has_category(string $category = '', int|WP_Post $post = null): bool { return false; }
function has_tag(string $tag = '', int|WP_Post $post = null): bool { return false; }

// URL API
function home_url(string $path = '', string|null $scheme = null): string { return ''; }
function site_url(string $path = '', string|null $scheme = null): string { return ''; }
function admin_url(string $path = '', string $scheme = 'admin'): string { return ''; }
function includes_url(string $path = ''): string { return ''; }
function content_url(string $path = ''): string { return ''; }
function plugins_url(string $path = '', string $plugin = ''): string { return ''; }
function wp_parse_url(string $url, int $component = -1): int|array|false { return false; }
function wp_validate_url(string $url): string|void { return $url; }
function esc_url(string $url, array $protocols = null, string $_context = 'display'): string { return $url; }
function esc_url_raw(string $url, array $protocols = null): string { return $url; }
function sanitize_url(string $url, string $protocols = null): string { return $url; }

// Sanitization
function sanitize_text_field(string $str): string { return $str; }
function sanitize_textarea_field(string $str): string { return $str; }
function sanitize_key(string $key): string { return $key; }
function sanitize_email(string $email): string { return $email; }
function sanitize_title(string $title, string $fallback_title = '', string $context = 'save'): string { return $title; }
function sanitize_title_with_dashes(string $title): string { return $title; }
function sanitize_mime_type(string $mime_type): string { return $mime_type; }
function sanitize_html_class(string $class, string $fallback = ''): string { return $class; }
function esc_html(string $text): string { return $text; }
function esc_html_e(string $text, string $domain = 'default'): void { echo esc_html($text); }
function esc_attr(string $text): string { return $text; }
function esc_attr_e(string $text, string $domain = 'default'): void { echo esc_attr($text); }
function esc_js(string $text): string { return $text; }

// Date/Time
function current_time(string $type, bool $gmt = false): int|string { return time(); }
function get_the_time(string $format = '', int|WP_Post $post = null, string $timezone = 'local'): string|false { return false; }
function get_the_date(string $format = '', int|WP_Post $post = null): string|false { return false; }
function get_post_datetime(int|WP_Post $post = null, string $field = 'date', string $timezone = 'local'): DateTime|false { return false; }
function date_i18n(string $dateformatstring, int|bool $unixtimestamp = false, bool $gmt = false): string { return date($dateformatstring); }
function human_time_diff(int $from, int $to = ''): string { return '1 hour'; }

// Localization
function __ (string $text, string $domain = 'default'): string { return $text; }
function _e(string $text, string $domain = 'default'): void { echo __($text, $domain); }
function _n(string $single, string $plural, int $number, string $domain = 'default'): string { return $number == 1 ? $single : $plural; }
function _x(string $text, string $context, string $domain = 'default'): string { return $text; }
function _nx(string $single, string $plural, int $number, string $context, string $domain = 'default'): string { return $number == 1 ? $single : $plural; }
function esc_html__(string $text, string $domain = 'default'): string { return esc_html($text); }
function esc_html_e(string $text, string $domain = 'default'): void { echo esc_html__($text, $domain); }
function esc_html_x(string $text, string $context, string $domain = 'default'): string { return esc_html(_x($text, $context, $domain)); }
function esc_attr__(string $text, string $domain = 'default'): string { return esc_attr($text); }
function esc_attr_e(string $text, string $domain = 'default'): void { echo esc_attr__($text, $domain); }
function esc_attr_x(string $text, string $context, string $domain = 'default'): string { return esc_attr(_x($text, $context, $domain)); }

// HTTP API
function wp_remote_get(string $url, array $args = []): array|WP_Error { return []; }
function wp_remote_post(string $url, array $args = []): array|WP_Error { return []; }
function wp_remote_request(string $url, array $args = []): array|WP_Error { return []; }
function wp_remote_retrieve_body(array $response): string { return ''; }
function wp_remote_retrieve_headers(array $response): array { return []; }
function wp_remote_retrieve_header(array $response, string $header): string { return ''; }
function wp_remote_retrieve_response_code(array $response): int { return 200; }
function wp_remote_retrieve_response_message(array $response): string { return 'OK'; }
function is_wp_error($thing): bool { return false; }

// Plugin API
function plugin_dir_path(string $file): string { return dirname($file) . '/'; }
function plugin_dir_url(string $file): string { return ''; }
function plugin_basename(string $file): string { return basename($file); }
function register_activation_hook(string $file, callable $function): void {}
function register_deactivation_hook(string $file, callable $function): void {}
function register_uninstall_hook(string $file, callable $callback): void {}

// Hooks
function add_action(string $hook, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool { return true; }
function remove_action(string $hook, callable $function_to_remove, int $priority = 10): bool { return true; }
function add_filter(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool { return true; }
function remove_filter(string $tag, callable $function_to_remove, int $priority = 10): bool { return true; }
function apply_filters(string $tag, mixed $value, mixed ...$args): mixed { return $value; }
function apply_filters_ref_array(string $tag, array $args, callable $function): mixed { return $value; }
function do_action(string $tag, mixed ...$arg): void {}
function do_action_ref_array(string $tag, array $args): void {}
function did_action(string $hook): int { return 0; }

// Nonces
function wp_create_nonce(string $action = -1): string { return 'nonce'; }
function wp_verify_nonce(string $nonce, int $action = -1): int|false { return 1; }
function check_ajax_referer(string $nonce, bool $query_arg = false, bool $die = true): int|false { return 1; }

// Security
function wp_hash(string $data, string $scheme = 'auth'): string { return hash('sha256', $data); }
function wp_hash_password(string $password): string { return password_hash($password, PASSWORD_DEFAULT); }
function wp_check_password(string $password, string $hash, int $user_id): bool { return true; }
function wp_set_password(string $password, int $user_id): void {}

// Utils
function is_admin(): bool { return false; }
function is_ssl(): bool { return false; }
function is_multisite(): bool { return false; }
function is_main_site(): bool { return false; }
function is_subdomain_install(): bool { return false; }
function is_blog_installed(): bool { return true; }
function is_wp_error($thing): bool { return $thing instanceof WP_Error; }
function is_array($maybe_array): bool { return is_array($maybe_array); }
function is_string($maybe_string): bool { return is_string($maybe_string); }
function is_int($maybe_int): bool { return is_int($maybe_int); }
function is_bool($maybe_bool): bool { return is_bool($maybe_bool); }
function is_object($maybe_object): bool { return is_object($maybe_object); }
function is_numeric($maybe_number): bool { return is_numeric($maybe_number); }
function is_email($email): bool { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function is_url($maybe_url): bool { return filter_var($maybe_url, FILTER_VALIDATE_URL) !== false; }
function is_serialized($data, bool $strict = true): bool { return false; }
function is_serialized_string($data): bool { return false; }
function is_404(): bool { return false; }
function is_search(): bool { return false; }
function is_archive(): bool { return false; }
function is_front_page(): bool { return false; }
function is_home(): bool { return false; }
function is_category(int|string $category = ''): bool { return false; }
function is_tag(int|string $tag = ''): bool { return false; }
function is_tax(string $taxonomy = '', int|string $term = ''): bool { return false; }
function is_author(int|string $author = ''): bool { return false; }
function is_date(): bool { return false; }
function is_day(): bool { return false; }
function is_month(): bool { return false; }
function is_year(): bool { return false; }
function is_comments_open(int|WP_Post $post = null): bool { return false; }
function pings_open(int|WP_Post $post = null): bool { return false; }

// Filesystem
function file_exists(string $filename): bool { return file_exists($filename); }
function is_readable(string $filename): bool { return is_readable($filename); }
function is_writable(string $filename): bool { return is_writable($filename); }
function get_filesystem_method(): string { return 'direct'; }
function get_file_description(string $file): string { return ''; }
function get_filesystem_direct(): object { return new class { public function exists($path) { return file_exists($path); } public function is_readable($path) { return is_readable($path); } public function is_writable($path) { return is_writable($path); } public function get_contents($path) { return file_get_contents($path); } public function put_contents($path, $data, $flags = 0) { return file_put_contents($path, $data, $flags); } }; }
function request_filesystem_credentials(bool $form_post, string $type = '', bool $error = false, string $context = '', bool $allow_relaxed_file_ownership = false): array|false { return false; }

// XML/RSS
function fetch_feed(string $url): SimplePie|false { return false; }
function get_feed_link(string $feed): string { return ''; }

// Cache
function wp_cache_get(string $key, string $group = '', bool $force = false, bool &$found = null): mixed { return null; }
function wp_cache_set(string $key, mixed $data, string $group = '', int $expire = 0): bool { return true; }
function wp_cache_delete(string $key, string $group = ''): bool { return true; }
function wp_cache_flush(): bool { return true; }

// Cron
function wp_schedule_event(int $timestamp, string $recurrence, string $hook, array $args = []): int|false { return 1; }
function wp_schedule_single_event(int $timestamp, string $hook, array $args = []): int|false { return 1; }
function wp_clear_scheduled_hook(string $hook, array $args = []): void {}
function wp_next_scheduled(string $hook, array $args = []): int|false { return false; }
function wp_get_scheduled_event(string $hook, array $args = []): object|false { return false; }
function wp_get_ready_cron_jobs(): array { return []; }

// Multisite
function get_sites(array $args = []): array { return []; }
function get_site(int $site_id = null): ?object { return null; }
function get_current_blog_id(): int { return 1; }
function switch_to_blog(int $new_blog, bool $deprecated = null): bool { return true; }
function restore_current_blog(): bool { return true; }

// Object Classes
class WP_Error {
    public function __construct(string $code = '', string $message = '', mixed $data = '') {}
    public function get(string $code = ''): mixed { return null; }
    public function get_error_code(): string { return ''; }
    public function get_error_message(string $code = ''): string { return ''; }
    public function get_error_data(string $code = ''): mixed { return null; }
    public function add(string $code, string $message, mixed $data = ''): self { return $this; }
    public function add_data(mixed $data, string $code = ''): self { return $this; }
}

class WP_User {
    public $ID = 0;
    public $data;
    public function __construct(int $id = 0, string $name = '', bool $site_id = '') {}
}

class WP_Post {
    public $ID = 0;
    public $post_author = '';
    public $post_date = '';
    public $post_date_gmt = '';
    public $post_content = '';
    public $post_title = '';
    public $post_excerpt = '';
    public $post_status = '';
    public $comment_status = '';
    public $ping_status = '';
    public $post_password = '';
    public $post_name = '';
    public $to_ping = '';
    public $pinged = '';
    public $post_modified = '';
    public $post_modified_gmt = '';
    public $post_content_filtered = '';
    public $post_parent = 0;
    public $guid = '';
    public $menu_order = 0;
    public $post_type = '';
    public $post_mime_type = '';
    public $comment_count = 0;
    public function __get(string $name): mixed { return null; }
    public function __isset(string $name): bool { return isset($this->$name); }
}

class WP_Term {
    public $term_id = 0;
    public $name = '';
    public $slug = '';
    public $term_group = 0;
    public $term_taxonomy_id = 0;
    public $taxonomy = '';
    public $description = '';
    public $parent = 0;
    public $count = 0;
}

class WP_Post_Type {
    public $name = '';
    public $labels = new stdClass();
    public $description = '';
    public $public = false;
    public $hierarchical = false;
    public $exclude_from_search = false;
    public $publicly_queryable = false;
    public $show_ui = false;
    public $show_in_menu = false;
    public $show_in_nav_menus = false;
    public $show_in_admin_bar = false;
    public $show_in_rest = false;
    public $rest_base = '';
    public $rest_controller_class = '';
    public $rest_controller = '';
    public $map_meta_cap = false;
    public $cap = new stdClass();
    public $capabilities = new stdClass();
    public $rewrite = false;
    public $query_var = '';
    public $can_export = false;
    public $delete_with_user = false;
    public $template = [];
    public $template_lock = false;
    public $_builtin = false;
}

class SimplePie {
    public function get_title(): ?string { return null; }
    public function get_permalink(): ?string { return null; }
    public function get_date(string $format = 'U'): string|false { return false; }
}

// WordPress Version
function get_bloginfo(string $show = '', string $filter = 'raw'): string { return ''; }
function bloginfo(string $show = ''): void {}

// Constants
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', false);
}
if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', false);
}
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}
if (!defined('WPMU_PLUGIN_DIR')) {
    define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
}
if (!defined('PLUGINDIR')) {
    define('PLUGINDIR', 'wp-content/plugins');
}
if (!defined('WP_LANG_DIR')) {
    define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
}
