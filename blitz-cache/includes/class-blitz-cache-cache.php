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

    /**
     * Get cached content with error handling.
     *
     * @param string $key Cache key.
     * @return string|null Cached content or null if not found/expired.
     */
    public function get_cached(string $key): ?string {
        try {
            $file = $this->cache_dir . $key . '.html';
            $file_gz = $file . '.gz';

            // Check if gzip version exists and client accepts
            if (!empty($this->options['gzip_enabled']) &&
                file_exists($file_gz) &&
                strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {

                $meta = $this->get_meta($key);
                if ($meta && time() < $meta['expires']) {
                    $content = @file_get_contents($file_gz);
                    if ($content !== false) {
                        $this->record_hit();
                        header('Content-Encoding: gzip');
                        header('X-Blitz-Cache: HIT (gzip)');
                        return $content;
                    } else {
                        if (function_exists('Blitz_Cache_Logger')) {
                            Blitz_Cache_Logger::get_instance()->warning(
                                'Failed to read gzipped cache file',
                                ['key' => $key, 'file' => $file_gz]
                            );
                        }
                    }
                }
            }

            // Fallback to regular HTML
            if (file_exists($file)) {
                $meta = $this->get_meta($key);
                if ($meta && time() < $meta['expires']) {
                    $content = @file_get_contents($file);
                    if ($content !== false) {
                        $this->record_hit();
                        header('X-Blitz-Cache: HIT');
                        return $content;
                    } else {
                        if (function_exists('Blitz_Cache_Logger')) {
                            Blitz_Cache_Logger::get_instance()->warning(
                                'Failed to read cache file',
                                ['key' => $key, 'file' => $file]
                            );
                        }
                    }
                }
            }

            $this->record_miss();
            return null;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in get_cached()',
                    [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]
                );
            }
            $this->record_miss();
            return null;
        }
    }

    /**
     * Store HTML content in cache with error handling.
     *
     * @param string $key  Cache key.
     * @param string $html HTML content to cache.
     * @return bool True on success, false on failure.
     */
    public function store(string $key, string $html): bool {
        try {
            // Apply filters before storing
            $html = apply_filters('blitz_cache_html_before_store', $html);

            // Minify if enabled
            if (!empty($this->options['html_minify_enabled'])) {
                if (class_exists('Blitz_Cache_Minify')) {
                    $minifier = new Blitz_Cache_Minify();
                    $html = $minifier->minify($html);
                }
            }

            // Add cache signature
            $html .= "\n<!-- Cached by Blitz Cache on " . gmdate('Y-m-d H:i:s') . " UTC -->";

            $file = $this->cache_dir . $key . '.html';

            // Validate cache directory is writable
            if (!is_dir($this->cache_dir)) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Cache directory does not exist',
                        ['cache_dir' => $this->cache_dir]
                    );
                }
                return false;
            }

            if (!is_writable($this->cache_dir)) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Cache directory is not writable',
                        ['cache_dir' => $this->cache_dir]
                    );
                }
                return false;
            }

            // Store regular HTML with atomic write
            $temp_file = $file . '.tmp.' . uniqid();
            $result = @file_put_contents($temp_file, $html, LOCK_EX);
            if ($result === false) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to write cache file',
                        ['key' => $key, 'file' => $file]
                    );
                }
                @unlink($temp_file);
                return false;
            }

            if (!@rename($temp_file, $file)) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to rename temp cache file',
                        ['temp_file' => $temp_file, 'file' => $file]
                    );
                }
                @unlink($temp_file);
                return false;
            }

            // Set secure permissions
            @chmod($file, 0644);

            // Store gzipped version if enabled
            if (!empty($this->options['gzip_enabled'])) {
                if (!function_exists('gzencode')) {
                    if (function_exists('Blitz_Cache_Logger')) {
                        Blitz_Cache_Logger::get_instance()->warning(
                            'gzencode function not available',
                            ['key' => $key]
                        );
                    }
                } else {
                    $gzipped = @gzencode($html, 9);
                    if ($gzipped !== false) {
                        $temp_gz = $file . '.gz.tmp.' . uniqid();
                        $result = @file_put_contents($temp_gz, $gzipped, LOCK_EX);
                        if ($result !== false) {
                            @rename($temp_gz, $file . '.gz');
                            @chmod($file . '.gz', 0644);
                        } else {
                            @unlink($temp_gz);
                        }
                    }
                }
            }

            // Update meta
            $this->set_meta($key, [
                'url' => $this->get_current_url(),
                'file' => $key . '.html',
                'created' => time(),
                'expires' => time() + ($this->options['page_cache_ttl'] ?? 86400),
                'mobile' => wp_is_mobile() && !empty($this->options['mobile_cache']),
            ]);

            // Update stats asynchronously (don't block on this)
            wp_schedule_single_event(time() + 5, 'blitz_cache_update_stats_async');

            // Action hook
            do_action('blitz_cache_after_store', $key, $html);

            return true;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in store()',
                    [
                        'key' => $key,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]
                );
            }
            return false;
        }
    }

    /**
     * Delete cached files with error handling.
     *
     * @param string $key Cache key.
     * @return bool True on success, false on failure.
     */
    public function delete(string $key): bool {
        try {
            $file = $this->cache_dir . $key . '.html';

            $deleted = false;

            if (file_exists($file)) {
                if (@unlink($file)) {
                    $deleted = true;
                } else {
                    if (function_exists('Blitz_Cache_Logger')) {
                        Blitz_Cache_Logger::get_instance()->warning(
                            'Failed to delete cache file',
                            ['key' => $key, 'file' => $file]
                        );
                    }
                }
            }

            if (file_exists($file . '.gz')) {
                if (@unlink($file . '.gz')) {
                    $deleted = true;
                } else {
                    if (function_exists('Blitz_Cache_Logger')) {
                        Blitz_Cache_Logger::get_instance()->warning(
                            'Failed to delete gzipped cache file',
                            ['key' => $key, 'file' => $file . '.gz']
                        );
                    }
                }
            }

            $this->delete_meta($key);
            return $deleted;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in delete()',
                    [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]
                );
            }
            return false;
        }
    }

    public function purge_url(string $url): void {
        $key = md5($url);
        $this->delete($key);

        // Also delete mobile version
        $this->delete(md5($url . '|mobile'));

        do_action('blitz_cache_after_purge_url', $url);
    }

    /**
     * Purge all cached files with error handling.
     *
     * @return bool True on success, false on failure.
     */
    public function purge_all(): bool {
        try {
            $files = @glob($this->cache_dir . '*.html*');
            $deleted_count = 0;
            $error_count = 0;

            if ($files) {
                foreach ($files as $file) {
                    if (@unlink($file)) {
                        $deleted_count++;
                    } else {
                        $error_count++;
                        if (function_exists('Blitz_Cache_Logger')) {
                            Blitz_Cache_Logger::get_instance()->warning(
                                'Failed to delete cache file during purge_all',
                                ['file' => $file]
                            );
                        }
                    }
                }
            }

            // Reset meta with atomic write
            $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';
            $temp_meta = $meta_file . '.tmp.' . uniqid();
            @file_put_contents($temp_meta, '{}', LOCK_EX);
            @rename($temp_meta, $meta_file);
            @chmod($meta_file, 0644);

            // Update stats
            $this->update_cache_stats();

            if (function_exists('Blitz_Cache_Logger')) {
                if ($error_count > 0) {
                    Blitz_Cache_Logger::get_instance()->warning(
                        'Purge completed with some errors',
                        [
                            'deleted' => $deleted_count,
                            'errors' => $error_count,
                        ]
                    );
                } else {
                    Blitz_Cache_Logger::get_instance()->info(
                        'Purge completed successfully',
                        ['deleted' => $deleted_count]
                    );
                }
            }

            do_action('blitz_cache_after_purge');
            return $error_count === 0;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in purge_all()',
                    [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]
                );
            }
            return false;
        }
    }

    /**
     * Get metadata for a cached item with error handling.
     *
     * @param string $key Cache key.
     * @return array|null Metadata or null if not found.
     */
    private function get_meta(string $key): ?array {
        try {
            $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';

            if (!file_exists($meta_file)) {
                return null;
            }

            $content = @file_get_contents($meta_file);
            if ($content === false) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->warning(
                        'Failed to read meta file',
                        ['meta_file' => $meta_file]
                    );
                }
                return null;
            }

            $meta = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->warning(
                        'Invalid JSON in meta file',
                        [
                            'meta_file' => $meta_file,
                            'json_error' => json_last_error_msg(),
                        ]
                    );
                }
                return null;
            }

            return $meta[$key] ?? null;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in get_meta()',
                    [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]
                );
            }
            return null;
        }
    }

    /**
     * Set metadata for a cached item with atomic write.
     *
     * @param string $key  Cache key.
     * @param array  $data Metadata to store.
     * @return bool True on success, false on failure.
     */
    private function set_meta(string $key, array $data): bool {
        try {
            $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';
            $meta = [];

            if (file_exists($meta_file)) {
                $content = @file_get_contents($meta_file);
                if ($content !== false) {
                    $meta = json_decode($content, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $meta = [];
                    }
                }
            }

            $meta[$key] = $data;

            // Atomic write
            $temp_file = $meta_file . '.tmp.' . uniqid();
            $json_content = wp_json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($json_content === false) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to encode meta to JSON',
                        ['key' => $key]
                    );
                }
                return false;
            }

            $result = @file_put_contents($temp_file, $json_content, LOCK_EX);
            if ($result === false) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to write temp meta file',
                        ['temp_file' => $temp_file]
                    );
                }
                @unlink($temp_file);
                return false;
            }

            if (!@rename($temp_file, $meta_file)) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to rename temp meta file',
                        ['temp_file' => $temp_file, 'meta_file' => $meta_file]
                    );
                }
                @unlink($temp_file);
                return false;
            }

            @chmod($meta_file, 0644);
            return true;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in set_meta()',
                    [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]
                );
            }
            return false;
        }
    }

    /**
     * Delete metadata for a cached item with atomic write.
     *
     * @param string $key Cache key.
     * @return bool True on success, false on failure.
     */
    private function delete_meta(string $key): bool {
        try {
            $meta_file = BLITZ_CACHE_CACHE_DIR . 'meta.json';

            if (!file_exists($meta_file)) {
                return true;
            }

            $content = @file_get_contents($meta_file);
            if ($content === false) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->warning(
                        'Failed to read meta file for delete',
                        ['meta_file' => $meta_file]
                    );
                }
                return false;
            }

            $meta = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $meta = [];
            }

            unset($meta[$key]);

            // Atomic write
            $temp_file = $meta_file . '.tmp.' . uniqid();
            $json_content = wp_json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($json_content === false) {
                return false;
            }

            @file_put_contents($temp_file, $json_content, LOCK_EX);
            @rename($temp_file, $meta_file);
            @chmod($meta_file, 0644);

            return true;

        } catch (Exception $e) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Exception in delete_meta()',
                    [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]
                );
            }
            return false;
        }
    }

    private function get_current_url(): string {
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Record cache hit with buffered stats updates.
     *
     * Uses in-memory buffering to reduce disk I/O.
     */
    private function record_hit(): void {
        static $hit_count = 0;
        $hit_count++;

        do_action('blitz_cache_hit');

        // Only write to disk every 10 hits to reduce I/O
        if ($hit_count % 10 === 0) {
            wp_schedule_single_event(time(), 'blitz_cache_update_stats_async', ['hits' => 10]);
        }
    }

    /**
     * Record cache miss with buffered stats updates.
     *
     * Uses in-memory buffering to reduce disk I/O.
     */
    private function record_miss(): void {
        static $miss_count = 0;
        $miss_count++;

        do_action('blitz_cache_miss');

        // Only write to disk every 10 misses to reduce I/O
        if ($miss_count % 10 === 0) {
            wp_schedule_single_event(time(), 'blitz_cache_update_stats_async', ['misses' => 10]);
        }
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
