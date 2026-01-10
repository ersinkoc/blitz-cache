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

    /**
     * Get all related URLs for a post to purge.
     *
     * @param int     $post_id Post ID.
     * @param \WP_Post $post    Post object.
     * @return array List of URLs to purge.
     */
    private function get_related_urls(int $post_id, \WP_Post $post): array {
        $urls = [];

        // Core URLs
        $urls = array_merge($urls, $this->get_core_urls($post_id));

        // Archive URLs
        $urls = array_merge($urls, $this->get_archive_urls($post_id, $post));

        // Taxonomy URLs
        $urls = array_merge($urls, $this->get_taxonomy_urls($post_id));

        // Feed URLs
        $urls = array_merge($urls, $this->get_feed_urls());

        return array_unique(array_filter($urls));
    }

    /**
     * Get core URLs (post itself, home, blog page).
     *
     * @param int $post_id Post ID.
     * @return array Core URLs.
     */
    private function get_core_urls(int $post_id): array {
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

        return $urls;
    }

    /**
     * Get archive URLs (post type, author, date).
     *
     * @param int     $post_id Post ID.
     * @param \WP_Post $post    Post object.
     * @return array Archive URLs.
     */
    private function get_archive_urls(int $post_id, \WP_Post $post): array {
        $urls = [];

        // Post type archive
        $post_type = get_post_type($post_id);
        $archive_link = get_post_type_archive_link($post_type);
        if ($archive_link) {
            $urls[] = $archive_link;
        }

        // Author archive
        $urls[] = get_author_posts_url($post->post_author);

        // Date archives
        $urls = array_merge($urls, $this->get_date_archive_urls($post_id));

        return $urls;
    }

    /**
     * Get date archive URLs for a post.
     *
     * @param int $post_id Post ID.
     * @return array Date archive URLs.
     */
    private function get_date_archive_urls(int $post_id): array {
        $year = get_the_date('Y', $post_id);
        $month = get_the_date('m', $post_id);
        $day = get_the_date('d', $post_id);

        return [
            get_year_link($year),
            get_month_link($year, $month),
            get_day_link($year, $month, $day),
        ];
    }

    /**
     * Get taxonomy URLs (categories, tags).
     *
     * @param int $post_id Post ID.
     * @return array Taxonomy URLs.
     */
    private function get_taxonomy_urls(int $post_id): array {
        $urls = [];

        // Category archives
        $categories = get_the_category($post_id);
        if ($categories) {
            foreach ($categories as $cat) {
                $urls[] = get_category_link($cat->term_id);
            }
        }

        // Tag archives
        $tags = get_the_tags($post_id);
        if ($tags) {
            foreach ($tags as $tag) {
                $urls[] = get_tag_link($tag->term_id);
            }
        }

        return $urls;
    }

    /**
     * Get feed URLs.
     *
     * @return array Feed URLs.
     */
    private function get_feed_urls(): array {
        return [
            get_feed_link(),
            get_feed_link('rdf'),
            get_feed_link('atom'),
        ];
    }

    /**
     * Update purge statistics with path validation.
     *
     * Validates the stats file path to prevent directory traversal attacks
     * and performs atomic write operations.
     */
    private function update_purge_stats(): void {
        $stats_file = BLITZ_CACHE_CACHE_DIR . 'stats.json';

        // Validate path to prevent traversal attacks
        $real_path = realpath($stats_file);
        $cache_real_path = realpath(BLITZ_CACHE_CACHE_DIR);

        // If file doesn't exist yet, validate parent directory
        if ($real_path === false) {
            $parent_dir = dirname($stats_file);
            $real_parent = realpath($parent_dir);

            if ($real_parent === false || strpos($real_parent, $cache_real_path) !== 0) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Invalid stats file path detected - parent directory validation failed',
                        [
                            'stats_file' => $stats_file,
                            'parent_dir' => $parent_dir,
                            'cache_dir' => BLITZ_CACHE_CACHE_DIR,
                        ]
                    );
                }
                return;
            }
        } else {
            // File exists, validate it's within cache directory
            if (strpos($real_path, $cache_real_path) !== 0) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Invalid stats file path detected - path outside cache directory',
                        [
                            'stats_file' => $stats_file,
                            'real_path' => $real_path,
                            'cache_dir' => BLITZ_CACHE_CACHE_DIR,
                        ]
                    );
                }
                return;
            }
        }

        // Read existing stats with error handling
        if (file_exists($stats_file)) {
            $content = @file_get_contents($stats_file);
            if ($content === false) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to read stats file',
                        ['stats_file' => $stats_file]
                    );
                }
                return;
            }
            $stats = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->warning(
                        'Invalid JSON in stats file, using defaults',
                        [
                            'stats_file' => $stats_file,
                            'json_error' => json_last_error_msg(),
                        ]
                    );
                }
                $stats = [];
            }
        } else {
            $stats = [];
        }

        // Update last purge time
        $stats['last_purge'] = time();

        // Atomic write using temp file
        $temp_file = $stats_file . '.tmp.' . uniqid();
        $json_content = wp_json_encode($stats);

        if ($json_content === false) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Failed to encode stats to JSON',
                    ['stats' => $stats]
                );
            }
            return;
        }

        $result = @file_put_contents($temp_file, $json_content, LOCK_EX);
        if ($result === false) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Failed to write temp stats file',
                    ['temp_file' => $temp_file]
                );
            }
            @unlink($temp_file);
            return;
        }

        // Atomic rename
        if (!@rename($temp_file, $stats_file)) {
            if (function_exists('Blitz_Cache_Logger')) {
                Blitz_Cache_Logger::get_instance()->error(
                    'Failed to rename temp stats file',
                    ['temp_file' => $temp_file, 'stats_file' => $stats_file]
                );
            }
            @unlink($temp_file);
            return;
        }

        // Set secure permissions
        @chmod($stats_file, 0644);
    }
}
