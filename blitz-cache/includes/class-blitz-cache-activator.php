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
                $created = wp_mkdir_p($dir);
                if (!$created) {
                    if (function_exists('Blitz_Cache_Logger')) {
                        Blitz_Cache_Logger::get_instance()->error(
                            'Failed to create cache directory',
                            ['dir' => $dir]
                        );
                    }
                    continue;
                }

                // Set secure permissions for directories (0755)
                @chmod($dir, 0755);

                // Verify permissions were set correctly
                $perms = fileperms($dir) & 0777;
                if ($perms !== 0755) {
                    if (function_exists('Blitz_Cache_Logger')) {
                        Blitz_Cache_Logger::get_instance()->warning(
                            'Could not set secure permissions on cache directory',
                            ['dir' => $dir, 'actual_perms' => decoct($perms)]
                        );
                    }
                }
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
            // Set secure permissions for .htaccess (0644)
            @chmod($htaccess, 0644);
        }

        $index = BLITZ_CACHE_CACHE_DIR . 'index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden');
            // Set secure permissions for index.php (0644)
            @chmod($index, 0644);
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

    /**
     * Enable WP_CACHE constant in wp-config.php with backup mechanism.
     *
     * Creates a backup of wp-config.php before modification and stores
     * the backup location for potential rollback.
     */
    private static function enable_wp_cache(): void {
        $config_file = ABSPATH . 'wp-config.php';
        $backup_dir = WP_CONTENT_DIR . '/blitz-cache-backups/';

        if (!is_writable($config_file)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>';
                echo esc_html__('Blitz Cache: Please add define(\'WP_CACHE\', true); to your wp-config.php', 'blitz-cache');
                echo '</p></div>';
            });
            return;
        }

        // Create backup directory if it doesn't exist
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
            // Protect backup directory with .htaccess
            file_put_contents($backup_dir . '.htaccess', "Deny from all\n");
            // Set secure permissions
            @chmod($backup_dir, 0755);
        }

        // Create backup before modifying
        $backup_file = $backup_dir . 'wp-config.php.backup.' . time();
        if (!copy($config_file, $backup_file)) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Failed to create wp-config.php backup',
                    ['config_file' => $config_file]
                );
            }
            return;
        }

        // Set secure permissions on backup
        @chmod($backup_file, 0644);

        // Store backup location in option for potential rollback
        update_option('blitz_cache_config_backup', $backup_file);

        // Read current config
        $config = file_get_contents($config_file);

        if ($config === false) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Failed to read wp-config.php',
                    ['config_file' => $config_file]
                );
            }
            return;
        }

        // Check if WP_CACHE is already defined
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

        // Atomic write using temp file
        $temp_file = $config_file . '.tmp.' . uniqid();
        if (file_put_contents($temp_file, $config) === false) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Failed to write temp wp-config.php',
                    ['temp_file' => $temp_file]
                );
            }
            @unlink($temp_file);
            return;
        }

        // Atomic rename
        if (!rename($temp_file, $config_file)) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->critical(
                    'Failed to rename temp wp-config.php to original',
                    ['temp_file' => $temp_file, 'config_file' => $config_file]
                );
            }
            @unlink($temp_file);

            // Rollback from backup
            if (copy($backup_file, $config_file)) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->info(
                        'Rolled back wp-config.php from backup',
                        ['backup_file' => $backup_file]
                    );
                }
            }
            return;
        }

        // Verify the change was written correctly
        $verify = file_get_contents($config_file);
        if ($verify === false || strpos($verify, "define('WP_CACHE', true)") === false) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->critical(
                    'Verification failed - WP_CACHE not found in wp-config.php after write',
                    ['config_file' => $config_file]
                );
            }
            // Rollback from backup
            copy($backup_file, $config_file);
            return;
        }

        // Set secure permissions
        @chmod($config_file, 0644);

        if (function_exists('Blitz_Cache_Logger')) {
            Blitz_Cache_Logger::get_instance()->info(
                'Successfully enabled WP_CACHE in wp-config.php',
                ['backup_file' => $backup_file]
            );
        }
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
