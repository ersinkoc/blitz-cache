# WordPress Plugin: Blitz Cache

## Overview

Blitz Cache is a zero-configuration WordPress caching plugin with Cloudflare Edge integration. It delivers lightning-fast page loads through intelligent file-based caching, automatic Cloudflare purge, and optional Workers edge caching. The philosophy: "Competitors overwhelm with complexity, we dominate with simplicity."

**Tagline:** Zero-config WordPress caching with Cloudflare Edge

## Technical Requirements

- WordPress: 6.0+
- PHP: 8.0+
- Dependencies: None (zero external dependencies)
- License: GPLv2+

## File Structure

```
blitz-cache/
├── blitz-cache.php                          # Main plugin file
├── advanced-cache.php                       # Dropin template (copied to wp-content)
├── uninstall.php                            # Uninstall handler with user choice
├── readme.txt                               # WordPress.org readme
├── LICENSE                                  # GPLv2
├── includes/
│   ├── class-blitz-cache.php               # Main orchestrator class
│   ├── class-blitz-cache-loader.php        # Hook/filter loader
│   ├── class-blitz-cache-activator.php     # Activation logic
│   ├── class-blitz-cache-deactivator.php   # Deactivation logic
│   ├── class-blitz-cache-i18n.php          # Internationalization
│   ├── class-blitz-cache-options.php       # Settings management
│   ├── class-blitz-cache-cache.php         # Core caching engine
│   ├── class-blitz-cache-purge.php         # Cache purge logic
│   ├── class-blitz-cache-warmup.php        # Cache preloader
│   ├── class-blitz-cache-minify.php        # HTML minification
│   ├── class-blitz-cache-cloudflare.php    # Cloudflare API integration
│   ├── class-blitz-cache-updater.php       # GitHub self-updater
│   └── integrations/
│       ├── class-blitz-cache-woocommerce.php
│       ├── class-blitz-cache-edd.php
│       └── class-blitz-cache-learndash.php
├── admin/
│   ├── class-blitz-cache-admin.php         # Admin controller
│   ├── class-blitz-cache-admin-bar.php     # Admin bar integration
│   ├── class-blitz-cache-dashboard-widget.php
│   ├── partials/
│   │   ├── dashboard.php                   # Dashboard tab view
│   │   ├── settings.php                    # Settings tab view
│   │   ├── cloudflare.php                  # Cloudflare tab view
│   │   ├── tools.php                       # Tools tab view
│   │   └── uninstall-modal.php             # Uninstall confirmation
│   ├── css/
│   │   └── blitz-cache-admin.css
│   └── js/
│       └── blitz-cache-admin.js
├── languages/
│   └── blitz-cache.pot
└── assets/
    └── icon.svg                            # Plugin icon (lightning bolt)
```

## Database Schema

No custom tables. All data stored in `wp_options`:

### Option: `blitz_cache_settings`

```php
[
    // Page Cache
    'page_cache_enabled' => true,
    'page_cache_ttl' => 86400,              // 24 hours in seconds
    'cache_logged_in' => false,
    'mobile_cache' => false,
    
    // Browser Cache
    'browser_cache_enabled' => true,
    'css_js_ttl' => 2592000,                // 30 days
    'images_ttl' => 7776000,                // 90 days
    
    // Compression
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    
    // Exclusions
    'excluded_urls' => [],                   // Array of URL patterns
    'excluded_cookies' => [
        'wordpress_logged_in_*',
        'woocommerce_cart_hash',
        'woocommerce_items_in_cart'
    ],
    'excluded_user_agents' => [],
    
    // Preload
    'warmup_enabled' => true,
    'warmup_source' => 'sitemap',           // 'sitemap' | 'menu' | 'custom'
    'warmup_interval' => 21600,             // 6 hours
    'warmup_batch_size' => 5,
    
    // Update Channel
    'update_channel' => 'stable',           // 'stable' | 'stable_github' | 'beta'
]
```

### Option: `blitz_cache_cloudflare`

```php
[
    'api_token' => '',                      // Encrypted with wp_encrypt()
    'zone_id' => '',
    'email' => '',                          // Optional, for Global API Key
    'connection_status' => 'disconnected',  // 'connected' | 'disconnected' | 'error'
    'last_purge' => 0,                      // Unix timestamp
    'workers_enabled' => false,
    'workers_route' => '',
]
```

### Option: `blitz_cache_stats` (Transient-based)

```php
[
    'hits' => 0,
    'misses' => 0,
    'cached_pages' => 0,
    'cache_size' => 0,                      // Bytes
    'last_warmup' => 0,
    'last_purge' => 0,
    'period_start' => 0,                    // Reset weekly
]
```

### Option: `blitz_cache_uninstall_preference`

Stored during uninstall modal: `'keep'` | `'delete'`

## Cache File Structure

```
wp-content/cache/blitz-cache/
├── pages/
│   ├── {md5-hash}.html                    # Cached HTML
│   └── {md5-hash}.html.gz                 # Pre-compressed version
├── meta.json                               # URL → file mapping + timestamps
├── stats.json                              # Hit/miss counters
├── .htaccess                               # Security rules
└── index.php                               # Silence is golden
```

### meta.json Structure

```json
{
  "https://example.com/": {
    "file": "a1b2c3d4.html",
    "created": 1704067200,
    "expires": 1704153600,
    "mobile": false
  }
}
```

## Class Specifications

### 1. blitz-cache.php (Main Plugin File)

```php
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
```

### 2. class-blitz-cache.php (Main Orchestrator)

```php
<?php
class Blitz_Cache {
    protected Blitz_Cache_Loader $loader;
    protected string $plugin_name = 'blitz-cache';
    protected string $version;

    public function __construct() {
        $this->version = BLITZ_CACHE_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_cache_hooks();
        $this->load_integrations();
    }

    private function load_dependencies(): void {
        $this->loader = new Blitz_Cache_Loader();
    }

    private function set_locale(): void {
        $i18n = new Blitz_Cache_I18n();
        $this->loader->add_action('plugins_loaded', $i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks(): void {
        if (!is_admin()) return;
        
        $admin = new Blitz_Cache_Admin($this->plugin_name, $this->version);
        $admin_bar = new Blitz_Cache_Admin_Bar();
        $dashboard = new Blitz_Cache_Dashboard_Widget();
        
        // Admin menu & pages
        $this->loader->add_action('admin_menu', $admin, 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        
        // Admin bar
        $this->loader->add_action('admin_bar_menu', $admin_bar, 'add_menu', 100);
        
        // Dashboard widget
        $this->loader->add_action('wp_dashboard_setup', $dashboard, 'register_widget');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_blitz_cache_purge_all', $admin, 'ajax_purge_all');
        $this->loader->add_action('wp_ajax_blitz_cache_purge_url', $admin, 'ajax_purge_url');
        $this->loader->add_action('wp_ajax_blitz_cache_warmup', $admin, 'ajax_warmup');
        $this->loader->add_action('wp_ajax_blitz_cache_save_settings', $admin, 'ajax_save_settings');
        $this->loader->add_action('wp_ajax_blitz_cache_test_cloudflare', $admin, 'ajax_test_cloudflare');
        $this->loader->add_action('wp_ajax_blitz_cache_save_cloudflare', $admin, 'ajax_save_cloudflare');
        
        // GitHub updater
        $updater = new Blitz_Cache_Updater();
        $this->loader->add_filter('pre_set_site_transient_update_plugins', $updater, 'check_update');
        $this->loader->add_filter('plugins_api', $updater, 'plugin_info', 10, 3);
    }

    private function define_public_hooks(): void {
        // Browser cache headers
        $this->loader->add_action('send_headers', [$this, 'send_browser_cache_headers']);
    }

    private function define_cache_hooks(): void {
        $cache = new Blitz_Cache_Cache();
        $purge = new Blitz_Cache_Purge();
        $warmup = new Blitz_Cache_Warmup();

        // Auto-purge on content changes
        $this->loader->add_action('save_post', $purge, 'on_post_save', 10, 2);
        $this->loader->add_action('delete_post', $purge, 'on_post_delete');
        $this->loader->add_action('switch_theme', $purge, 'purge_all');
        $this->loader->add_action('customize_save_after', $purge, 'purge_all');
        $this->loader->add_action('update_option_blogname', $purge, 'purge_all');
        $this->loader->add_action('update_option_blogdescription', $purge, 'purge_all');
        $this->loader->add_action('update_option_permalink_structure', $purge, 'purge_all');

        // Scheduled warmup
        $this->loader->add_action('blitz_cache_warmup_cron', $warmup, 'run');

        // Comment changes
        $this->loader->add_action('comment_post', $purge, 'on_comment_change');
        $this->loader->add_action('edit_comment', $purge, 'on_comment_change');
        $this->loader->add_action('delete_comment', $purge, 'on_comment_change');
        $this->loader->add_action('wp_set_comment_status', $purge, 'on_comment_change');
    }

    private function load_integrations(): void {
        // WooCommerce
        if (class_exists('WooCommerce')) {
            $woo = new Blitz_Cache_WooCommerce();
            $woo->init($this->loader);
        }

        // Easy Digital Downloads
        if (class_exists('Easy_Digital_Downloads')) {
            $edd = new Blitz_Cache_EDD();
            $edd->init($this->loader);
        }

        // LearnDash
        if (class_exists('SFWD_LMS')) {
            $ld = new Blitz_Cache_LearnDash();
            $ld->init($this->loader);
        }
    }

    public function send_browser_cache_headers(): void {
        $options = Blitz_Cache_Options::get();
        if (!$options['browser_cache_enabled']) return;
        
        // Handled via .htaccess for static files
        // This is for dynamic PHP-served content fallback
    }

    public function run(): void {
        $this->loader->run();
    }
}
```

### 3. class-blitz-cache-loader.php

```php
<?php
class Blitz_Cache_Loader {
    protected array $actions = [];
    protected array $filters = [];

    public function add_action(string $hook, object $component, string $callback, int $priority = 10, int $args = 1): void {
        $this->actions[] = compact('hook', 'component', 'callback', 'priority', 'args');
    }

    public function add_filter(string $hook, object $component, string $callback, int $priority = 10, int $args = 1): void {
        $this->filters[] = compact('hook', 'component', 'callback', 'priority', 'args');
    }

    public function run(): void {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['args']);
        }
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['args']);
        }
    }
}
```

### 4. class-blitz-cache-activator.php

```php
<?php
class Blitz_Cache_Activator {
    public static function activate(): void {
        // Check requirements
        self::check_requirements();
        
        // Create cache directory
        self::create_cache_directory();
        
        // Install advanced-cache.php dropin
        self::install_dropin();
        
        // Set default options
        self::set_default_options();
        
        // Enable WP_CACHE constant
        self::enable_wp_cache();
        
        // Schedule warmup cron
        self::schedule_cron();
        
        // Trigger activation hook
        do_action('blitz_cache_activated');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    private static function check_requirements(): void {
        global $wp_version;
        
        if (version_compare(PHP_VERSION, BLITZ_CACHE_MIN_PHP, '<')) {
            deactivate_plugins(BLITZ_CACHE_PLUGIN_BASENAME);
            wp_die(sprintf(
                __('Blitz Cache requires PHP %s or higher. You are running PHP %s.', 'blitz-cache'),
                BLITZ_CACHE_MIN_PHP,
                PHP_VERSION
            ));
        }
        
        if (version_compare($wp_version, BLITZ_CACHE_MIN_WP, '<')) {
            deactivate_plugins(BLITZ_CACHE_PLUGIN_BASENAME);
            wp_die(sprintf(
                __('Blitz Cache requires WordPress %s or higher.', 'blitz-cache'),
                BLITZ_CACHE_MIN_WP
            ));
        }
    }

    private static function create_cache_directory(): void {
        $dirs = [
            BLITZ_CACHE_CACHE_DIR,
            BLITZ_CACHE_CACHE_DIR . 'pages/',
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }

        // Security files
        $htaccess = BLITZ_CACHE_CACHE_DIR . '.htaccess';
        if (!file_exists($htaccess)) {
            $rules = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !\.html$
    RewriteCond %{REQUEST_FILENAME} !\.html\.gz$
    RewriteRule . - [F,L]
</IfModule>

<IfModule mod_authz_core.c>
    <FilesMatch "\.(json|php)$">
        Require all denied
    </FilesMatch>
</IfModule>
HTACCESS;
            file_put_contents($htaccess, $rules);
        }

        $index = BLITZ_CACHE_CACHE_DIR . 'index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden');
        }

        // Initialize meta and stats files
        if (!file_exists(BLITZ_CACHE_CACHE_DIR . 'meta.json')) {
            file_put_contents(BLITZ_CACHE_CACHE_DIR . 'meta.json', '{}');
        }
        if (!file_exists(BLITZ_CACHE_CACHE_DIR . 'stats.json')) {
            file_put_contents(BLITZ_CACHE_CACHE_DIR . 'stats.json', json_encode([
                'hits' => 0,
                'misses' => 0,
                'cached_pages' => 0,
                'cache_size' => 0,
                'last_warmup' => 0,
                'last_purge' => 0,
                'period_start' => time(),
            ]));
        }
    }

    private static function install_dropin(): void {
        $source = BLITZ_CACHE_PLUGIN_DIR . 'advanced-cache.php';
        $dest = WP_CONTENT_DIR . '/advanced-cache.php';

        // Backup existing if not ours
        if (file_exists($dest)) {
            $content = file_get_contents($dest);
            if (strpos($content, 'BLITZ_CACHE') === false) {
                rename($dest, $dest . '.backup.' . time());
            }
        }

        copy($source, $dest);
    }

    private static function set_default_options(): void {
        $defaults = [
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'cache_logged_in' => false,
            'mobile_cache' => false,
            'browser_cache_enabled' => true,
            'css_js_ttl' => 2592000,
            'images_ttl' => 7776000,
            'gzip_enabled' => true,
            'html_minify_enabled' => true,
            'excluded_urls' => [],
            'excluded_cookies' => ['wordpress_logged_in_*', 'woocommerce_cart_hash', 'woocommerce_items_in_cart'],
            'excluded_user_agents' => [],
            'warmup_enabled' => true,
            'warmup_source' => 'sitemap',
            'warmup_interval' => 21600,
            'warmup_batch_size' => 5,
            'update_channel' => 'stable',
        ];

        if (!get_option('blitz_cache_settings')) {
            add_option('blitz_cache_settings', $defaults, '', 'no');
        }

        if (!get_option('blitz_cache_cloudflare')) {
            add_option('blitz_cache_cloudflare', [
                'api_token' => '',
                'zone_id' => '',
                'email' => '',
                'connection_status' => 'disconnected',
                'last_purge' => 0,
                'workers_enabled' => false,
                'workers_route' => '',
            ], '', 'no');
        }
    }

    private static function enable_wp_cache(): void {
        $config_file = ABSPATH . 'wp-config.php';
        
        if (!is_writable($config_file)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>';
                echo esc_html__('Blitz Cache: Please add define(\'WP_CACHE\', true); to your wp-config.php', 'blitz-cache');
                echo '</p></div>';
            });
            return;
        }

        $config = file_get_contents($config_file);
        
        if (strpos($config, 'WP_CACHE') !== false) {
            // Already defined, try to set to true
            $config = preg_replace(
                "/define\s*\(\s*['\"]WP_CACHE['\"]\s*,\s*false\s*\)/",
                "define('WP_CACHE', true)",
                $config
            );
        } else {
            // Add after opening PHP tag
            $config = preg_replace(
                '/^<\?php/',
                "<?php\ndefine('WP_CACHE', true); // Added by Blitz Cache",
                $config
            );
        }

        file_put_contents($config_file, $config);
    }

    private static function schedule_cron(): void {
        if (!wp_next_scheduled('blitz_cache_warmup_cron')) {
            $options = get_option('blitz_cache_settings', []);
            $interval = $options['warmup_interval'] ?? 21600;
            wp_schedule_event(time(), 'blitz_cache_warmup', 'blitz_cache_warmup_cron');
        }

        // Register custom interval
        add_filter('cron_schedules', function($schedules) {
            $options = get_option('blitz_cache_settings', []);
            $interval = $options['warmup_interval'] ?? 21600;
            $schedules['blitz_cache_warmup'] = [
                'interval' => $interval,
                'display' => __('Blitz Cache Warmup Interval', 'blitz-cache'),
            ];
            return $schedules;
        });
    }
}
```

### 5. class-blitz-cache-deactivator.php

```php
<?php
class Blitz_Cache_Deactivator {
    public static function deactivate(): void {
        // Clear scheduled events
        wp_clear_scheduled_hook('blitz_cache_warmup_cron');
        
        // Remove advanced-cache.php dropin
        $dropin = WP_CONTENT_DIR . '/advanced-cache.php';
        if (file_exists($dropin)) {
            $content = file_get_contents($dropin);
            if (strpos($content, 'BLITZ_CACHE') !== false) {
                unlink($dropin);
            }
        }
        
        // Disable WP_CACHE (optional - leave enabled for other cache plugins)
        // self::disable_wp_cache();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
```

### 6. class-blitz-cache-options.php

```php
<?php
class Blitz_Cache_Options {
    private static ?array $settings = null;
    private static ?array $cloudflare = null;
    
    public static function get(string $key = ''): mixed {
        if (self::$settings === null) {
            self::$settings = get_option('blitz_cache_settings', []);
        }
        
        if ($key === '') {
            return self::$settings;
        }
        
        return self::$settings[$key] ?? null;
    }

    public static function set(array $settings): bool {
        self::$settings = array_merge(self::$settings ?? [], $settings);
        return update_option('blitz_cache_settings', self::$settings);
    }

    public static function get_cloudflare(string $key = ''): mixed {
        if (self::$cloudflare === null) {
            self::$cloudflare = get_option('blitz_cache_cloudflare', []);
            
            // Decrypt token
            if (!empty(self::$cloudflare['api_token'])) {
                self::$cloudflare['api_token'] = self::decrypt(self::$cloudflare['api_token']);
            }
        }
        
        if ($key === '') {
            return self::$cloudflare;
        }
        
        return self::$cloudflare[$key] ?? null;
    }

    public static function set_cloudflare(array $settings): bool {
        // Encrypt token before saving
        if (!empty($settings['api_token'])) {
            $settings['api_token'] = self::encrypt($settings['api_token']);
        }
        
        self::$cloudflare = array_merge(self::$cloudflare ?? [], $settings);
        return update_option('blitz_cache_cloudflare', self::$cloudflare);
    }

    private static function encrypt(string $data): string {
        if (!function_exists('openssl_encrypt')) {
            return base64_encode($data);
        }
        
        $key = wp_salt('auth');
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    private static function decrypt(string $data): string {
        if (!function_exists('openssl_decrypt')) {
            return base64_decode($data);
        }
        
        $key = wp_salt('auth');
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv) ?: '';
    }

    public static function get_defaults(): array {
        return [
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'cache_logged_in' => false,
            'mobile_cache' => false,
            'browser_cache_enabled' => true,
            'css_js_ttl' => 2592000,
            'images_ttl' => 7776000,
            'gzip_enabled' => true,
            'html_minify_enabled' => true,
            'excluded_urls' => [],
            'excluded_cookies' => ['wordpress_logged_in_*', 'woocommerce_cart_hash', 'woocommerce_items_in_cart'],
            'excluded_user_agents' => [],
            'warmup_enabled' => true,
            'warmup_source' => 'sitemap',
            'warmup_interval' => 21600,
            'warmup_batch_size' => 5,
            'update_channel' => 'stable',
        ];
    }

    public static function reset(): void {
        self::$settings = self::get_defaults();
        update_option('blitz_cache_settings', self::$settings);
    }
}
```

### 7. class-blitz-cache-cache.php (Core Caching Engine)

```php
<?php
class Blitz_Cache_Cache {
    private string $cache_dir;
    private array $options;

    public function __construct() {
        $this->cache_dir = BLITZ_CACHE_CACHE_DIR . 'pages/';
        $this->options = Blitz_Cache_Options::get();
    }

    public function should_cache(): bool {
        // Filter hook for external control
        $should_cache = apply_filters('blitz_cache_should_cache', true);
        if (!$should_cache) {
            return false;
        }

        // Check if caching enabled
        if (empty($this->options['page_cache_enabled'])) {
            return false;
        }

        // Only cache GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return false;
        }

        // Don't cache logged-in users (unless enabled)
        if (is_user_logged_in() && empty($this->options['cache_logged_in'])) {
            return false;
        }

        // Check for excluded cookies
        foreach ($this->options['excluded_cookies'] as $pattern) {
            foreach ($_COOKIE as $name => $value) {
                if (fnmatch($pattern, $name)) {
                    return false;
                }
            }
        }

        // Check for excluded URLs
        $current_url = $this->get_current_url();
        foreach ($this->options['excluded_urls'] as $pattern) {
            if (fnmatch($pattern, $current_url) || strpos($current_url, $pattern) !== false) {
                return false;
            }
        }

        // Check for excluded user agents
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        foreach ($this->options['excluded_user_agents'] as $pattern) {
            if (fnmatch($pattern, $user_agent)) {
                return false;
            }
        }

        // Don't cache POST data responses
        if (!empty($_POST)) {
            return false;
        }

        // Don't cache if there's a query string (unless it's allowed)
        if (!empty($_GET) && !apply_filters('blitz_cache_cache_query_strings', false)) {
            // Allow specific query params
            $allowed = apply_filters('blitz_cache_allowed_query_params', ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid']);
            $current_params = array_keys($_GET);
            $disallowed = array_diff($current_params, $allowed);
            if (!empty($disallowed)) {
                return false;
            }
        }

        return true;
    }

    public function get_cache_key(): string {
        $url = $this->get_current_url();
        
        // Optionally include mobile in cache key
        if (!empty($this->options['mobile_cache']) && wp_is_mobile()) {
            $url .= '|mobile';
        }

        return md5($url);
    }

    public function get_cached(string $key): ?string {
        $file = $this->cache_dir . $key . '.html';
        $file_gz = $file . '.gz';

        // Check if gzip version exists and client accepts
        if ($this->options['gzip_enabled'] && 
            file_exists($file_gz) && 
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
            
            $meta = $this->get_meta($key);
            if ($meta && time() < $meta['expires']) {
                $this->record_hit();
                header('Content-Encoding: gzip');
                header('X-Blitz-Cache: HIT (gzip)');
                return file_get_contents($file_gz);
            }
        }

        // Fallback to regular HTML
        if (file_exists($file)) {
            $meta = $this->get_meta($key);
            if ($meta && time() < $meta['expires']) {
                $this->record_hit();
                header('X-Blitz-Cache: HIT');
                return file_get_contents($file);
            }
        }

        $this->record_miss();
        return null;
    }

    public function store(string $key, string $html): void {
        // Apply filters before storing
        $html = apply_filters('blitz_cache_html_before_store', $html);

        // Minify if enabled
        if (!empty($this->options['html_minify_enabled'])) {
            $minifier = new Blitz_Cache_Minify();
            $html = $minifier->minify($html);
        }

        // Add cache signature
        $html .= "\n<!-- Cached by Blitz Cache on " . gmdate('Y-m-d H:i:s') . " UTC -->";

        $file = $this->cache_dir . $key . '.html';
        
        // Store regular HTML
        file_put_contents($file, $html);

        // Store gzipped version
        if (!empty($this->options['gzip_enabled'])) {
            file_put_contents($file . '.gz', gzencode($html, 9));
        }

        // Update meta
        $this->set_meta($key, [
            'url' => $this->get_current_url(),
            'file' => $key . '.html',
            'created' => time(),
            'expires' => time() + ($this->options['page_cache_ttl'] ?? 86400),
            'mobile' => wp_is_mobile() && !empty($this->options['mobile_cache']),
        ]);

        // Update stats
        $this->update_cache_stats();

        // Action hook
        do_action('blitz_cache_after_store', $key, $html);
    }

    public function delete(string $key): void {
        $file = $this->cache_dir . $key . '.html';
        
        if (file_exists($file)) {
            unlink($file);
        }
        if (file_exists($file . '.gz')) {
            unlink($file . '.gz');
        }

        $this->delete_meta($key);
    }

    public function purge_url(string $url): void {
        $key = md5($url);
        $this->delete($key);

        // Also delete mobile version
        $this->delete(md5($url . '|mobile'));

        do_action('blitz_cache_after_purge_url', $url);
    }

    public function purge_all(): void {
        $files = glob($this->cache_dir . '*.html*');
        foreach ($files as $file) {
            unlink($file);
        }

        // Reset meta
        file_put_contents(BLITZ_CACHE_CACHE_DIR . 'meta.json', '{}');
        
        // Update stats
        $this->update_cache_stats();

        do_action('blitz_cache_after_purge');
    }

    private function get_meta(string $key): ?array {
        $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';
        if (!file_exists($meta_file)) {
            return null;
        }

        $meta = json_decode(file_get_contents($meta_file), true);
        return $meta[$key] ?? null;
    }

    private function set_meta(string $key, array $data): void {
        $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';
        $meta = [];
        
        if (file_exists($meta_file)) {
            $meta = json_decode(file_get_contents($meta_file), true) ?: [];
        }

        $meta[$key] = $data;
        file_put_contents($meta_file, wp_json_encode($meta, JSON_PRETTY_PRINT));
    }

    private function delete_meta(string $key): void {
        $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';
        
        if (!file_exists($meta_file)) {
            return;
        }

        $meta = json_decode(file_get_contents($meta_file), true) ?: [];
        unset($meta[$key]);
        file_put_contents($meta_file, wp_json_encode($meta, JSON_PRETTY_PRINT));
    }

    private function get_current_url(): string {
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function record_hit(): void {
        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';
        $stats = json_decode(file_get_contents($stats_file), true) ?: [];
        $stats['hits'] = ($stats['hits'] ?? 0) + 1;
        file_put_contents($stats_file, wp_json_encode($stats));
        
        do_action('blitz_cache_hit');
    }

    private function record_miss(): void {
        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';
        $stats = json_decode(file_get_contents($stats_file), true) ?: [];
        $stats['misses'] = ($stats['misses'] ?? 0) + 1;
        file_put_contents($stats_file, wp_json_encode($stats));
        
        do_action('blitz_cache_miss');
    }

    private function update_cache_stats(): void {
        $files = glob($this->cache_dir . '*.html');
        $size = 0;
        foreach ($files as $file) {
            $size += filesize($file);
        }

        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';
        $stats = json_decode(file_get_contents($stats_file), true) ?: [];
        $stats['cached_pages'] = count($files);
        $stats['cache_size'] = $size;
        file_put_contents($stats_file, wp_json_encode($stats));
    }

    public function get_stats(): array {
        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';
        if (!file_exists($stats_file)) {
            return [
                'hits' => 0,
                'misses' => 0,
                'cached_pages' => 0,
                'cache_size' => 0,
                'last_warmup' => 0,
                'last_purge' => 0,
                'period_start' => time(),
            ];
        }
        return json_decode(file_get_contents($stats_file), true);
    }
}
```

### 8. advanced-cache.php (Dropin Template)

```php
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

// Try to serve gzipped version
if (file_exists($cache_file_gz) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
    // Check expiry via meta
    $meta_file = $cache_dir . 'meta.json';
    if (file_exists($meta_file)) {
        $meta = json_decode(file_get_contents($meta_file), true);
        if (isset($meta[$cache_key]) && time() < $meta[$cache_key]['expires']) {
            header('Content-Type: text/html; charset=UTF-8');
            header('Content-Encoding: gzip');
            header('X-Blitz-Cache: HIT (gzip, dropin)');
            header('Vary: Accept-Encoding');
            readfile($cache_file_gz);
            exit;
        }
    }
}

// Try regular HTML
if (file_exists($cache_file)) {
    $meta_file = $cache_dir . 'meta.json';
    if (file_exists($meta_file)) {
        $meta = json_decode(file_get_contents($meta_file), true);
        if (isset($meta[$cache_key]) && time() < $meta[$cache_key]['expires']) {
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Blitz-Cache: HIT (dropin)');
            readfile($cache_file);
            exit;
        }
    }
}

// No cache hit - continue loading WordPress
// The plugin will handle caching the response
```

### 9. class-blitz-cache-purge.php

```php
<?php
class Blitz_Cache_Purge {
    private Blitz_Cache_Cache $cache;
    private ?Blitz_Cache_Cloudflare $cloudflare = null;

    public function __construct() {
        $this->cache = new Blitz_Cache_Cache();
        
        $cf_options = Blitz_Cache_Options::get_cloudflare();
        if (!empty($cf_options['api_token']) && $cf_options['connection_status'] === 'connected') {
            $this->cloudflare = new Blitz_Cache_Cloudflare();
        }
    }

    public function on_post_save(int $post_id, \WP_Post $post): void {
        // Don't purge on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Don't purge revisions
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Only published posts
        if ($post->post_status !== 'publish') {
            return;
        }

        $urls_to_purge = $this->get_related_urls($post_id, $post);
        $urls_to_purge = apply_filters('blitz_cache_purge_urls', $urls_to_purge, $post_id, $post);

        foreach ($urls_to_purge as $url) {
            $this->cache->purge_url($url);
        }

        // Cloudflare purge
        if ($this->cloudflare) {
            $this->cloudflare->purge_urls($urls_to_purge);
        }

        $this->update_purge_stats();
    }

    public function on_post_delete(int $post_id): void {
        $post = get_post($post_id);
        if (!$post) return;

        $urls_to_purge = $this->get_related_urls($post_id, $post);
        
        foreach ($urls_to_purge as $url) {
            $this->cache->purge_url($url);
        }

        if ($this->cloudflare) {
            $this->cloudflare->purge_urls($urls_to_purge);
        }
    }

    public function on_comment_change(int $comment_id): void {
        $comment = get_comment($comment_id);
        if (!$comment) return;

        $post_url = get_permalink($comment->comment_post_ID);
        if ($post_url) {
            $this->cache->purge_url($post_url);
            
            if ($this->cloudflare) {
                $this->cloudflare->purge_urls([$post_url]);
            }
        }
    }

    public function purge_all(): void {
        $this->cache->purge_all();

        if ($this->cloudflare) {
            $this->cloudflare->purge_all();
        }

        $this->update_purge_stats();
        
        do_action('blitz_cache_after_purge');
    }

    public function purge_url(string $url): void {
        $this->cache->purge_url($url);

        if ($this->cloudflare) {
            $this->cloudflare->purge_urls([$url]);
        }
    }

    private function get_related_urls(int $post_id, \WP_Post $post): array {
        $urls = [];

        // The post itself
        $permalink = get_permalink($post_id);
        if ($permalink) {
            $urls[] = $permalink;
        }

        // Home page
        $urls[] = home_url('/');

        // Blog page (if different from home)
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id) {
            $urls[] = get_permalink($blog_page_id);
        }

        // Post type archive
        $post_type = get_post_type($post_id);
        $archive_link = get_post_type_archive_link($post_type);
        if ($archive_link) {
            $urls[] = $archive_link;
        }

        // Category archives
        $categories = get_the_category($post_id);
        foreach ($categories as $cat) {
            $urls[] = get_category_link($cat->term_id);
        }

        // Tag archives
        $tags = get_the_tags($post_id);
        if ($tags) {
            foreach ($tags as $tag) {
                $urls[] = get_tag_link($tag->term_id);
            }
        }

        // Author archive
        $urls[] = get_author_posts_url($post->post_author);

        // Date archives
        $urls[] = get_year_link(get_the_date('Y', $post_id));
        $urls[] = get_month_link(get_the_date('Y', $post_id), get_the_date('m', $post_id));
        $urls[] = get_day_link(get_the_date('Y', $post_id), get_the_date('m', $post_id), get_the_date('d', $post_id));

        // Feed URLs
        $urls[] = get_feed_link();
        $urls[] = get_feed_link('rdf');
        $urls[] = get_feed_link('atom');

        return array_unique(array_filter($urls));
    }

    private function update_purge_stats(): void {
        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';
        $stats = json_decode(file_get_contents($stats_file), true) ?: [];
        $stats['last_purge'] = time();
        file_put_contents($stats_file, wp_json_encode($stats));
    }
}
```

### 10. class-blitz-cache-warmup.php

```php
<?php
class Blitz_Cache_Warmup {
    private array $options;

    public function __construct() {
        $this->options = Blitz_Cache_Options::get();
    }

    public function run(): void {
        if (empty($this->options['warmup_enabled'])) {
            return;
        }

        $urls = $this->get_urls();
        $urls = apply_filters('blitz_cache_warmup_urls', $urls);
        
        $batch_size = $this->options['warmup_batch_size'] ?? 5;
        $batches = array_chunk($urls, $batch_size);

        foreach ($batches as $batch) {
            foreach ($batch as $url) {
                $this->warm_url($url);
            }
            // Small delay between batches to avoid overloading server
            usleep(500000); // 0.5 seconds
        }

        $this->update_warmup_stats();
        
        do_action('blitz_cache_after_warmup', $urls);
    }

    public function warm_url(string $url): bool {
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'sslverify' => false,
            'headers' => [
                'Cache-Control' => 'no-cache',
                'X-Blitz-Warmup' => '1',
            ],
        ]);

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    private function get_urls(): array {
        $source = $this->options['warmup_source'] ?? 'sitemap';
        
        switch ($source) {
            case 'sitemap':
                return $this->get_sitemap_urls();
            case 'menu':
                return $this->get_menu_urls();
            case 'custom':
                return apply_filters('blitz_cache_custom_warmup_urls', []);
            default:
                return $this->get_sitemap_urls();
        }
    }

    private function get_sitemap_urls(): array {
        $urls = [];
        
        // Try WordPress native sitemap first (WP 5.5+)
        $sitemap_url = home_url('/wp-sitemap.xml');
        $response = wp_remote_get($sitemap_url, ['timeout' => 10]);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $urls = $this->parse_sitemap_index($body);
        }

        // Fallback: Yoast SEO sitemap
        if (empty($urls)) {
            $sitemap_url = home_url('/sitemap_index.xml');
            $response = wp_remote_get($sitemap_url, ['timeout' => 10]);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                $urls = $this->parse_sitemap_index($body);
            }
        }

        // Fallback: generate from posts
        if (empty($urls)) {
            $urls = $this->generate_url_list();
        }

        return array_slice($urls, 0, 500); // Limit to 500 URLs
    }

    private function parse_sitemap_index(string $xml): array {
        $urls = [];
        
        // Suppress XML errors
        libxml_use_internal_errors(true);
        $sitemap = simplexml_load_string($xml);
        
        if ($sitemap === false) {
            return [];
        }

        // Check if this is a sitemap index or direct sitemap
        if (isset($sitemap->sitemap)) {
            // Sitemap index - fetch each child sitemap
            foreach ($sitemap->sitemap as $child) {
                $child_urls = $this->fetch_sitemap((string)$child->loc);
                $urls = array_merge($urls, $child_urls);
            }
        } elseif (isset($sitemap->url)) {
            // Direct sitemap
            foreach ($sitemap->url as $url) {
                $urls[] = (string)$url->loc;
            }
        }

        return $urls;
    }

    private function fetch_sitemap(string $url): array {
        $response = wp_remote_get($url, ['timeout' => 10]);
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        libxml_use_internal_errors(true);
        $sitemap = simplexml_load_string($body);
        
        if ($sitemap === false || !isset($sitemap->url)) {
            return [];
        }

        $urls = [];
        foreach ($sitemap->url as $url) {
            $urls[] = (string)$url->loc;
        }

        return $urls;
    }

    private function get_menu_urls(): array {
        $urls = [home_url('/')];
        
        $locations = get_nav_menu_locations();
        foreach ($locations as $location => $menu_id) {
            if (!$menu_id) continue;
            
            $items = wp_get_nav_menu_items($menu_id);
            if (!$items) continue;
            
            foreach ($items as $item) {
                if (!empty($item->url)) {
                    $urls[] = $item->url;
                }
            }
        }

        return array_unique($urls);
    }

    private function generate_url_list(): array {
        $urls = [home_url('/')];

        // Get published posts
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        foreach ($posts as $post) {
            $urls[] = get_permalink($post->ID);
        }

        // Get pages
        $pages = get_pages(['number' => 50]);
        foreach ($pages as $page) {
            $urls[] = get_permalink($page->ID);
        }

        // Categories
        $categories = get_categories(['number' => 20]);
        foreach ($categories as $cat) {
            $urls[] = get_category_link($cat->term_id);
        }

        return array_unique($urls);
    }

    private function update_warmup_stats(): void {
        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';
        $stats = json_decode(file_get_contents($stats_file), true) ?: [];
        $stats['last_warmup'] = time();
        file_put_contents($stats_file, wp_json_encode($stats));
    }
}
```

### 11. class-blitz-cache-minify.php

```php
<?php
class Blitz_Cache_Minify {
    public function minify(string $html): string {
        // Apply filter to skip minification
        if (!apply_filters('blitz_cache_should_minify', true, $html)) {
            return $html;
        }

        // Preserve inline scripts and styles
        $preserved = [];
        $index = 0;

        // Preserve <pre>, <code>, <textarea>, <script>, <style>
        $preserve_tags = ['pre', 'code', 'textarea', 'script', 'style'];
        foreach ($preserve_tags as $tag) {
            $html = preg_replace_callback(
                "/<{$tag}[^>]*>.*?<\/{$tag}>/is",
                function($match) use (&$preserved, &$index) {
                    $placeholder = "<!--BLITZ_PRESERVE_{$index}-->";
                    $preserved[$placeholder] = $match[0];
                    $index++;
                    return $placeholder;
                },
                $html
            );
        }

        // Remove HTML comments (except IE conditionals and preserved placeholders)
        $html = preg_replace('/<!--(?!\[|BLITZ_PRESERVE_).*?-->/s', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '> <', $html);

        // Remove multiple spaces
        $html = preg_replace('/\s{2,}/', ' ', $html);

        // Remove whitespace around block elements
        $block_elements = 'html|head|body|div|section|article|header|footer|nav|aside|main|p|h[1-6]|ul|ol|li|table|tr|td|th|form|fieldset';
        $html = preg_replace("/\s*(<\/?(?:{$block_elements})[^>]*>)\s*/i", '$1', $html);

        // Remove newlines and tabs (convert to single space)
        $html = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $html);

        // Clean up multiple spaces again
        $html = preg_replace('/\s{2,}/', ' ', $html);

        // Restore preserved content
        foreach ($preserved as $placeholder => $content) {
            $html = str_replace($placeholder, $content, $html);
        }

        return trim($html);
    }
}
```

### 12. class-blitz-cache-cloudflare.php

```php
<?php
class Blitz_Cache_Cloudflare {
    private string $api_url = 'https://api.cloudflare.com/client/v4';
    private array $options;

    public function __construct() {
        $this->options = Blitz_Cache_Options::get_cloudflare();
    }

    public function test_connection(): array {
        $response = $this->request('GET', '/user/tokens/verify');
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        if ($response['success']) {
            // Update connection status
            Blitz_Cache_Options::set_cloudflare(['connection_status' => 'connected']);
            return [
                'success' => true,
                'message' => __('Connection successful!', 'blitz-cache'),
            ];
        }

        Blitz_Cache_Options::set_cloudflare(['connection_status' => 'error']);
        return [
            'success' => false,
            'message' => $response['errors'][0]['message'] ?? __('Unknown error', 'blitz-cache'),
        ];
    }

    public function get_zones(): array {
        $response = $this->request('GET', '/zones');
        
        if (is_wp_error($response) || !$response['success']) {
            return [];
        }

        $zones = [];
        foreach ($response['result'] as $zone) {
            $zones[] = [
                'id' => $zone['id'],
                'name' => $zone['name'],
                'status' => $zone['status'],
            ];
        }

        return $zones;
    }

    public function purge_all(): bool {
        if (empty($this->options['zone_id'])) {
            return false;
        }

        $response = $this->request('POST', "/zones/{$this->options['zone_id']}/purge_cache", [
            'purge_everything' => true,
        ]);

        $success = !is_wp_error($response) && ($response['success'] ?? false);
        
        if ($success) {
            Blitz_Cache_Options::set_cloudflare(['last_purge' => time()]);
            do_action('blitz_cache_cf_purge_success', 'all');
        } else {
            do_action('blitz_cache_cf_purge_failed', 'all', $response);
        }

        return $success;
    }

    public function purge_urls(array $urls): bool {
        if (empty($this->options['zone_id']) || empty($urls)) {
            return false;
        }

        // Cloudflare allows max 30 URLs per request
        $chunks = array_chunk($urls, 30);
        $success = true;

        foreach ($chunks as $chunk) {
            $response = $this->request('POST', "/zones/{$this->options['zone_id']}/purge_cache", [
                'files' => $chunk,
            ]);

            if (is_wp_error($response) || empty($response['success'])) {
                $success = false;
                do_action('blitz_cache_cf_purge_failed', 'urls', $response);
            }
        }

        if ($success) {
            do_action('blitz_cache_cf_purge_success', 'urls', $urls);
        }

        return $success;
    }

    public function get_workers_script(): string {
        // Return the Workers script for edge caching
        return <<<'JAVASCRIPT'
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
  const url = new URL(request.url)
  
  // Skip cache for admin, login, specific paths
  const skipPaths = ['/wp-admin', '/wp-login', '/wp-json', '/cart', '/checkout', '/my-account']
  if (skipPaths.some(path => url.pathname.startsWith(path))) {
    return fetch(request)
  }
  
  // Skip for logged-in users (check cookie)
  const cookies = request.headers.get('Cookie') || ''
  if (cookies.includes('wordpress_logged_in_') || cookies.includes('woocommerce_cart_hash')) {
    return fetch(request)
  }
  
  // Only cache GET requests
  if (request.method !== 'GET') {
    return fetch(request)
  }
  
  // Check cache
  const cache = caches.default
  let response = await cache.match(request)
  
  if (!response) {
    response = await fetch(request)
    
    // Only cache successful HTML responses
    const contentType = response.headers.get('Content-Type') || ''
    if (response.status === 200 && contentType.includes('text/html')) {
      const newResponse = new Response(response.body, response)
      newResponse.headers.set('X-Blitz-Edge-Cache', 'MISS')
      newResponse.headers.set('Cache-Control', 'public, max-age=86400')
      
      event.waitUntil(cache.put(request, newResponse.clone()))
      return newResponse
    }
    
    return response
  }
  
  // Add cache hit header
  const cachedResponse = new Response(response.body, response)
  cachedResponse.headers.set('X-Blitz-Edge-Cache', 'HIT')
  return cachedResponse
}
JAVASCRIPT;
    }

    private function request(string $method, string $endpoint, array $data = []): array|\WP_Error {
        $args = [
            'method' => $method,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['api_token'],
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($data)) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($this->api_url . $endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
```

### 13. class-blitz-cache-updater.php (GitHub Self-Updater)

```php
<?php
class Blitz_Cache_Updater {
    private string $github_repo = 'ersinkoc/blitz-cache'; // TODO: Set actual repo
    private string $plugin_file;
    private string $plugin_slug = 'blitz-cache';
    private ?object $github_response = null;

    public function __construct() {
        $this->plugin_file = BLITZ_CACHE_PLUGIN_BASENAME;
    }

    public function check_update(object $transient): object {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Only check GitHub updates if channel is not 'stable' (wp.org)
        $options = Blitz_Cache_Options::get();
        if (($options['update_channel'] ?? 'stable') === 'stable') {
            return $transient;
        }

        $release = $this->get_github_release();
        if (!$release) {
            return $transient;
        }

        $current_version = $transient->checked[$this->plugin_file] ?? BLITZ_CACHE_VERSION;
        
        if (version_compare($release->tag_name, $current_version, '>')) {
            $transient->response[$this->plugin_file] = (object)[
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_file,
                'new_version' => $release->tag_name,
                'url' => "https://github.com/{$this->github_repo}",
                'package' => $release->zipball_url,
                'icons' => [],
                'banners' => [],
                'tested' => '',
                'requires_php' => BLITZ_CACHE_MIN_PHP,
            ];
        }

        return $transient;
    }

    public function plugin_info(mixed $result, string $action, object $args): mixed {
        if ($action !== 'plugin_information' || ($args->slug ?? '') !== $this->plugin_slug) {
            return $result;
        }

        $release = $this->get_github_release();
        if (!$release) {
            return $result;
        }

        return (object)[
            'name' => 'Blitz Cache',
            'slug' => $this->plugin_slug,
            'version' => $release->tag_name,
            'author' => '<a href="https://github.com/ersinkoc">Ersin KOÇ</a>',
            'homepage' => "https://github.com/{$this->github_repo}",
            'download_link' => $release->zipball_url,
            'sections' => [
                'description' => 'Zero-config WordPress caching with Cloudflare Edge integration.',
                'changelog' => $this->format_changelog($release->body),
            ],
            'requires' => BLITZ_CACHE_MIN_WP,
            'requires_php' => BLITZ_CACHE_MIN_PHP,
            'tested' => get_bloginfo('version'),
            'last_updated' => $release->published_at,
        ];
    }

    private function get_github_release(): ?object {
        if ($this->github_response !== null) {
            return $this->github_response;
        }

        $options = Blitz_Cache_Options::get();
        $channel = $options['update_channel'] ?? 'stable';
        
        // Beta channel gets latest release (including prereleases)
        // Stable GitHub channel gets latest non-prerelease
        $endpoint = $channel === 'beta'
            ? "https://api.github.com/repos/{$this->github_repo}/releases"
            : "https://api.github.com/repos/{$this->github_repo}/releases/latest";

        $response = wp_remote_get($endpoint, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Blitz-Cache-Updater',
            ],
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        
        if ($channel === 'beta' && is_array($body)) {
            $this->github_response = $body[0] ?? null;
        } else {
            $this->github_response = $body;
        }

        return $this->github_response;
    }

    private function format_changelog(string $markdown): string {
        // Basic markdown to HTML conversion
        $html = nl2br(esc_html($markdown));
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.+<\/li>)/s', '<ul>$1</ul>', $html);
        return $html;
    }
}
```

### 14. class-blitz-cache-admin.php

```php
<?php
class Blitz_Cache_Admin {
    private string $plugin_name;
    private string $version;

    public function __construct(string $plugin_name, string $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function add_admin_menu(): void {
        add_menu_page(
            __('Blitz Cache', 'blitz-cache'),
            __('Blitz Cache', 'blitz-cache'),
            'manage_options',
            'blitz-cache',
            [$this, 'render_admin_page'],
            'dashicons-performance',
            80
        );
    }

    public function enqueue_styles(string $hook): void {
        if ($hook !== 'toplevel_page_blitz-cache') {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name,
            BLITZ_CACHE_PLUGIN_URL . 'admin/css/blitz-cache-admin.css',
            [],
            $this->version
        );
    }

    public function enqueue_scripts(string $hook): void {
        if ($hook !== 'toplevel_page_blitz-cache') {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name,
            BLITZ_CACHE_PLUGIN_URL . 'admin/js/blitz-cache-admin.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_localize_script($this->plugin_name, 'blitzCache', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('blitz_cache_nonce'),
            'strings' => [
                'purging' => __('Purging...', 'blitz-cache'),
                'purged' => __('Cache purged!', 'blitz-cache'),
                'warming' => __('Warming cache...', 'blitz-cache'),
                'warmed' => __('Cache warmed!', 'blitz-cache'),
                'saving' => __('Saving...', 'blitz-cache'),
                'saved' => __('Settings saved!', 'blitz-cache'),
                'error' => __('An error occurred.', 'blitz-cache'),
                'testing' => __('Testing connection...', 'blitz-cache'),
            ],
        ]);
    }

    public function render_admin_page(): void {
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
        $tabs = [
            'dashboard' => __('Dashboard', 'blitz-cache'),
            'settings' => __('Settings', 'blitz-cache'),
            'cloudflare' => __('Cloudflare', 'blitz-cache'),
            'tools' => __('Tools', 'blitz-cache'),
        ];

        echo '<div class="wrap blitz-cache-wrap">';
        echo '<h1><span class="dashicons dashicons-performance"></span> ' . esc_html__('Blitz Cache', 'blitz-cache') . '</h1>';
        
        // Tabs navigation
        echo '<nav class="nav-tab-wrapper">';
        foreach ($tabs as $tab_id => $tab_name) {
            $active_class = $active_tab === $tab_id ? 'nav-tab-active' : '';
            $url = add_query_arg('tab', $tab_id, admin_url('admin.php?page=blitz-cache'));
            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . esc_attr($active_class) . '">' . esc_html($tab_name) . '</a>';
        }
        echo '</nav>';

        // Tab content
        echo '<div class="blitz-cache-content">';
        switch ($active_tab) {
            case 'settings':
                include BLITZ_CACHE_PLUGIN_DIR . 'admin/partials/settings.php';
                break;
            case 'cloudflare':
                include BLITZ_CACHE_PLUGIN_DIR . 'admin/partials/cloudflare.php';
                break;
            case 'tools':
                include BLITZ_CACHE_PLUGIN_DIR . 'admin/partials/tools.php';
                break;
            default:
                include BLITZ_CACHE_PLUGIN_DIR . 'admin/partials/dashboard.php';
        }
        echo '</div>';
        echo '</div>';
    }

    public function ajax_purge_all(): void {
        check_ajax_referer('blitz_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'blitz-cache')]);
        }

        $purge = new Blitz_Cache_Purge();
        $purge->purge_all();

        wp_send_json_success(['message' => __('All cache purged successfully!', 'blitz-cache')]);
    }

    public function ajax_purge_url(): void {
        check_ajax_referer('blitz_cache_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'blitz-cache')]);
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        if (empty($url)) {
            wp_send_json_error(['message' => __('No URL provided.', 'blitz-cache')]);
        }

        $purge = new Blitz_Cache_Purge();
        $purge->purge_url($url);

        wp_send_json_success(['message' => __('Page cache purged!', 'blitz-cache')]);
    }

    public function ajax_warmup(): void {
        check_ajax_referer('blitz_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'blitz-cache')]);
        }

        $warmup = new Blitz_Cache_Warmup();
        $warmup->run();

        wp_send_json_success(['message' => __('Cache warmup completed!', 'blitz-cache')]);
    }

    public function ajax_save_settings(): void {
        check_ajax_referer('blitz_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'blitz-cache')]);
        }

        $settings = [
            'page_cache_enabled' => !empty($_POST['page_cache_enabled']),
            'page_cache_ttl' => absint($_POST['page_cache_ttl'] ?? 86400),
            'cache_logged_in' => !empty($_POST['cache_logged_in']),
            'mobile_cache' => !empty($_POST['mobile_cache']),
            'browser_cache_enabled' => !empty($_POST['browser_cache_enabled']),
            'css_js_ttl' => absint($_POST['css_js_ttl'] ?? 2592000),
            'images_ttl' => absint($_POST['images_ttl'] ?? 7776000),
            'gzip_enabled' => !empty($_POST['gzip_enabled']),
            'html_minify_enabled' => !empty($_POST['html_minify_enabled']),
            'excluded_urls' => $this->sanitize_textarea_to_array($_POST['excluded_urls'] ?? ''),
            'excluded_cookies' => $this->sanitize_textarea_to_array($_POST['excluded_cookies'] ?? ''),
            'excluded_user_agents' => $this->sanitize_textarea_to_array($_POST['excluded_user_agents'] ?? ''),
            'warmup_enabled' => !empty($_POST['warmup_enabled']),
            'warmup_source' => sanitize_key($_POST['warmup_source'] ?? 'sitemap'),
            'warmup_interval' => absint($_POST['warmup_interval'] ?? 21600),
            'warmup_batch_size' => absint($_POST['warmup_batch_size'] ?? 5),
            'update_channel' => sanitize_key($_POST['update_channel'] ?? 'stable'),
        ];

        Blitz_Cache_Options::set($settings);
        
        do_action('blitz_cache_settings_saved', $settings);

        wp_send_json_success(['message' => __('Settings saved!', 'blitz-cache')]);
    }

    public function ajax_test_cloudflare(): void {
        check_ajax_referer('blitz_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'blitz-cache')]);
        }

        $cloudflare = new Blitz_Cache_Cloudflare();
        $result = $cloudflare->test_connection();

        if ($result['success']) {
            $zones = $cloudflare->get_zones();
            wp_send_json_success([
                'message' => $result['message'],
                'zones' => $zones,
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    public function ajax_save_cloudflare(): void {
        check_ajax_referer('blitz_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'blitz-cache')]);
        }

        $settings = [
            'api_token' => sanitize_text_field($_POST['api_token'] ?? ''),
            'zone_id' => sanitize_text_field($_POST['zone_id'] ?? ''),
            'workers_enabled' => !empty($_POST['workers_enabled']),
            'workers_route' => sanitize_text_field($_POST['workers_route'] ?? ''),
        ];

        Blitz_Cache_Options::set_cloudflare($settings);

        wp_send_json_success(['message' => __('Cloudflare settings saved!', 'blitz-cache')]);
    }

    private function sanitize_textarea_to_array(string $input): array {
        $lines = explode("\n", sanitize_textarea_field($input));
        return array_filter(array_map('trim', $lines));
    }
}
```

### 15. Integration: class-blitz-cache-woocommerce.php

```php
<?php
class Blitz_Cache_WooCommerce {
    public function init(Blitz_Cache_Loader $loader): void {
        // Add WooCommerce-specific exclusions
        add_filter('blitz_cache_should_cache', [$this, 'should_cache']);
        add_filter('blitz_cache_excluded_urls', [$this, 'excluded_urls']);
        
        // Smart purge on product/order changes
        $loader->add_action('woocommerce_update_product', $this, 'on_product_update');
        $loader->add_action('woocommerce_product_set_stock', $this, 'on_stock_change');
        $loader->add_action('woocommerce_variation_set_stock', $this, 'on_stock_change');
    }

    public function should_cache(bool $should_cache): bool {
        if (!$should_cache) {
            return false;
        }

        // Don't cache cart, checkout, account pages
        if (function_exists('is_cart') && is_cart()) {
            return false;
        }
        if (function_exists('is_checkout') && is_checkout()) {
            return false;
        }
        if (function_exists('is_account_page') && is_account_page()) {
            return false;
        }

        return true;
    }

    public function excluded_urls(array $urls): array {
        $woo_urls = [
            '/cart/*',
            '/checkout/*',
            '/my-account/*',
            '/*add-to-cart=*',
            '/*remove_item=*',
        ];

        return array_merge($urls, $woo_urls);
    }

    public function on_product_update(int $product_id): void {
        $purge = new Blitz_Cache_Purge();
        
        // Purge product page
        $purge->purge_url(get_permalink($product_id));
        
        // Purge shop page
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id) {
            $purge->purge_url(get_permalink($shop_page_id));
        }

        // Purge product categories
        $terms = get_the_terms($product_id, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $purge->purge_url(get_term_link($term));
            }
        }
    }

    public function on_stock_change($product): void {
        $product_id = $product instanceof WC_Product ? $product->get_id() : $product;
        $this->on_product_update($product_id);
    }
}
```

### 16. Integration: class-blitz-cache-edd.php

```php
<?php
class Blitz_Cache_EDD {
    public function init(Blitz_Cache_Loader $loader): void {
        add_filter('blitz_cache_should_cache', [$this, 'should_cache']);
        add_filter('blitz_cache_excluded_urls', [$this, 'excluded_urls']);
        
        $loader->add_action('edd_save_download', $this, 'on_download_update');
    }

    public function should_cache(bool $should_cache): bool {
        if (!$should_cache) return false;

        if (function_exists('edd_is_checkout') && edd_is_checkout()) {
            return false;
        }

        return true;
    }

    public function excluded_urls(array $urls): array {
        $edd_urls = [
            '/checkout/*',
            '/purchase-history/*',
            '/purchase-confirmation/*',
            '/*edd_action=*',
        ];

        return array_merge($urls, $edd_urls);
    }

    public function on_download_update(int $download_id): void {
        $purge = new Blitz_Cache_Purge();
        $purge->purge_url(get_permalink($download_id));
        
        // Purge archive
        $archive = get_post_type_archive_link('download');
        if ($archive) {
            $purge->purge_url($archive);
        }
    }
}
```

### 17. Integration: class-blitz-cache-learndash.php

```php
<?php
class Blitz_Cache_LearnDash {
    public function init(Blitz_Cache_Loader $loader): void {
        add_filter('blitz_cache_should_cache', [$this, 'should_cache']);
        
        $loader->add_action('save_post_sfwd-courses', $this, 'on_course_update');
        $loader->add_action('save_post_sfwd-lessons', $this, 'on_lesson_update');
    }

    public function should_cache(bool $should_cache): bool {
        if (!$should_cache) return false;

        // Don't cache user-specific content
        if (is_singular(['sfwd-lessons', 'sfwd-topic', 'sfwd-quiz'])) {
            if (is_user_logged_in()) {
                return false;
            }
        }

        return true;
    }

    public function on_course_update(int $post_id): void {
        $purge = new Blitz_Cache_Purge();
        $purge->purge_url(get_permalink($post_id));
        $purge->purge_url(get_post_type_archive_link('sfwd-courses'));
    }

    public function on_lesson_update(int $post_id): void {
        $purge = new Blitz_Cache_Purge();
        $purge->purge_url(get_permalink($post_id));
        
        // Purge parent course
        $course_id = get_post_meta($post_id, 'course_id', true);
        if ($course_id) {
            $purge->purge_url(get_permalink($course_id));
        }
    }
}
```

### 18. uninstall.php

```php
<?php
/**
 * Blitz Cache Uninstall
 * 
 * This file runs when the plugin is deleted from WordPress.
 */

// Exit if not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check user preference
$preference = get_option('blitz_cache_uninstall_preference', '');

// If no preference set, default to keep (shouldn't happen but safety first)
if (empty($preference)) {
    $preference = 'keep';
}

if ($preference === 'delete') {
    // Delete all options
    delete_option('blitz_cache_settings');
    delete_option('blitz_cache_cloudflare');
    delete_option('blitz_cache_uninstall_preference');
    
    // Delete transients
    delete_transient('blitz_cache_stats');
    
    // Delete cache directory
    $cache_dir = WP_CONTENT_DIR . '/cache/blitz-cache/';
    if (is_dir($cache_dir)) {
        blitz_cache_rmdir_recursive($cache_dir);
    }
    
    // Remove advanced-cache.php if ours
    $dropin = WP_CONTENT_DIR . '/advanced-cache.php';
    if (file_exists($dropin)) {
        $content = file_get_contents($dropin);
        if (strpos($content, 'BLITZ_CACHE') !== false) {
            unlink($dropin);
        }
    }
    
    // Try to remove WP_CACHE constant
    $config_file = ABSPATH . 'wp-config.php';
    if (is_writable($config_file)) {
        $config = file_get_contents($config_file);
        $config = preg_replace("/define\s*\(\s*['\"]WP_CACHE['\"]\s*,\s*true\s*\)\s*;\s*\/\/\s*Added by Blitz Cache\n?/", '', $config);
        file_put_contents($config_file, $config);
    }
}

// Always delete the preference option itself
delete_option('blitz_cache_uninstall_preference');

/**
 * Recursively delete directory
 */
function blitz_cache_rmdir_recursive(string $dir): void {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? blitz_cache_rmdir_recursive($path) : unlink($path);
    }
    rmdir($dir);
}
```

## Hook Reference

| Hook Name | Type | Parameters | Description |
|-----------|------|------------|-------------|
| `blitz_cache_should_cache` | Filter | `(bool $should_cache)` | Control whether current request should be cached |
| `blitz_cache_ttl` | Filter | `(int $ttl, string $url)` | Modify TTL for specific URLs |
| `blitz_cache_excluded_urls` | Filter | `(array $urls)` | Add URLs to exclusion list |
| `blitz_cache_excluded_cookies` | Filter | `(array $cookies)` | Add cookies to exclusion list |
| `blitz_cache_html_before_store` | Filter | `(string $html)` | Modify HTML before caching |
| `blitz_cache_html_before_serve` | Filter | `(string $html)` | Modify HTML before serving from cache |
| `blitz_cache_purge_urls` | Filter | `(array $urls, int $post_id, WP_Post $post)` | Modify URLs to purge on post save |
| `blitz_cache_warmup_urls` | Filter | `(array $urls)` | Modify URLs for cache warmup |
| `blitz_cache_settings_defaults` | Filter | `(array $defaults)` | Override default settings |
| `blitz_cache_should_minify` | Filter | `(bool $should, string $html)` | Control HTML minification |
| `blitz_cache_cache_query_strings` | Filter | `(bool $cache)` | Allow caching URLs with query strings |
| `blitz_cache_allowed_query_params` | Filter | `(array $params)` | Query params that don't bust cache |
| `blitz_cache_after_purge` | Action | `()` | After all cache is purged |
| `blitz_cache_after_purge_url` | Action | `(string $url)` | After specific URL is purged |
| `blitz_cache_after_warmup` | Action | `(array $urls)` | After cache warmup completes |
| `blitz_cache_after_store` | Action | `(string $key, string $html)` | After HTML is stored in cache |
| `blitz_cache_activated` | Action | `()` | After plugin activation |
| `blitz_cache_settings_saved` | Action | `(array $settings)` | After settings are saved |
| `blitz_cache_hit` | Action | `()` | On cache hit |
| `blitz_cache_miss` | Action | `()` | On cache miss |
| `blitz_cache_cf_purge_success` | Action | `(string $type, array $urls = [])` | After successful CF purge |
| `blitz_cache_cf_purge_failed` | Action | `(string $type, mixed $response)` | After failed CF purge |

## Admin UI Specifications

### Dashboard Tab

Display:
- Cache status indicator (Active/Inactive)
- Quick stats: Cached Pages, Hit Ratio, Cache Size
- Last warmup timestamp
- Last purge timestamp
- Quick action buttons: Purge All, Warmup Now
- Cloudflare connection status

### Settings Tab

All fields visible with smart defaults:

**Page Cache Section:**
- Enable Page Cache (toggle, default: ON)
- Cache TTL (number input, default: 24 hours)
- Cache Logged-in Users (toggle, default: OFF)
- Separate Mobile Cache (toggle, default: OFF)

**Browser Cache Section:**
- Enable Browser Cache (toggle, default: ON)
- CSS/JS TTL (number input, default: 30 days)
- Images TTL (number input, default: 90 days)

**Compression Section:**
- Enable GZIP (toggle, default: ON)
- Enable HTML Minify (toggle, default: ON)

**Exclusions Section:**
- Excluded URLs (textarea, one per line)
- Excluded Cookies (textarea, one per line)
- Excluded User Agents (textarea, one per line)

**Preload Section:**
- Enable Cache Warmup (toggle, default: ON)
- Warmup Source (select: Sitemap/Menu/Custom)
- Warmup Interval (select: 2h/6h/12h/24h)

### Cloudflare Tab

- API Token (password input)
- Test Connection button
- Zone selector (dropdown, populated after connection)
- Workers Edge Cache (toggle)
- Workers Route (text input, e.g., "example.com/*")
- Workers Script preview (readonly textarea)
- Guided setup instructions

### Tools Tab

- Update Channel (radio: Stable WP.org / Stable GitHub / Beta GitHub)
- Export Settings (button)
- Import Settings (file upload)
- Reset to Defaults (button with confirmation)
- Debug Information (collapsible)

## Security Implementation

1. **All AJAX handlers** verify nonce via `check_ajax_referer()`
2. **All AJAX handlers** check capability (`manage_options` or `edit_posts`)
3. **All settings** sanitized with appropriate `sanitize_*` functions
4. **All output** escaped with `esc_html()`, `esc_attr()`, `esc_url()`
5. **CF API token** encrypted with AES-256-CBC before storage
6. **Cache directory** protected with `.htaccess` and `index.php`
7. **File operations** use `wp_normalize_path()` to prevent traversal
8. **Cache key** is MD5 hash, not raw URL

## Testing Checklist

### Activation
- [ ] Creates cache directory structure
- [ ] Installs advanced-cache.php dropin
- [ ] Adds WP_CACHE constant to wp-config.php
- [ ] Sets default options
- [ ] Schedules warmup cron

### Caching
- [ ] Caches homepage on first visit
- [ ] Serves cached version on second visit (check X-Blitz-Cache header)
- [ ] Does not cache logged-in users (by default)
- [ ] Does not cache POST requests
- [ ] Does not cache excluded URLs
- [ ] Respects TTL expiration

### Purging
- [ ] Purges on post save
- [ ] Purges related archives on post save
- [ ] Purges on comment changes
- [ ] Admin bar "Purge This Page" works
- [ ] "Purge All" clears entire cache

### Warmup
- [ ] Cron job triggers warmup
- [ ] Manual warmup button works
- [ ] Respects batch size setting

### Cloudflare
- [ ] Test connection validates token
- [ ] Zone list populates correctly
- [ ] Purge All triggers CF purge
- [ ] Per-URL purge triggers CF purge
- [ ] Workers script generates correctly

### Integrations
- [ ] WooCommerce cart/checkout excluded
- [ ] WooCommerce product update triggers purge
- [ ] EDD checkout excluded
- [ ] LearnDash user content excluded

### Deactivation
- [ ] Removes advanced-cache.php dropin
- [ ] Clears scheduled cron

### Uninstall
- [ ] Shows preference modal
- [ ] "Keep settings" preserves data
- [ ] "Delete all" removes everything

## Notes for Claude Code

1. **File creation order**: Start with main plugin file, then constants, then classes in dependency order
2. **Namespace**: No namespace used (WordPress convention for broader compatibility)
3. **Coding standards**: Follow WordPress PHP Coding Standards
4. **String translation**: All user-facing strings wrapped in `__()` or `esc_html__()`
5. **GitHub repo**: Replace `ersinkoc` placeholders with actual GitHub username
6. **Author info**: Replace `Ersin KOÇ` and `https://github.com/ersinkoc` placeholders
7. **Testing**: Test on clean WordPress 6.0+ install with PHP 8.0+
