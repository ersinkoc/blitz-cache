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

    /**
     * Validate and sanitize tab parameter with whitelist.
     *
     * @param string $tab Raw tab value from $_GET.
     * @return string Validated tab key.
     */
    private function get_valid_tab(string $tab): string {
        $allowed_tabs = ['dashboard', 'settings', 'cloudflare', 'tools'];
        $tab = sanitize_key($tab);
        return in_array($tab, $allowed_tabs, true) ? $tab : 'dashboard';
    }

    public function render_admin_page(): void {
        $active_tab = isset($_GET['tab']) ? $this->get_valid_tab($_GET['tab']) : 'dashboard';
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
