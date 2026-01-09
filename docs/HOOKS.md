# Blitz Cache Hooks Reference

This document provides a comprehensive reference for all hooks (filters and actions) available in Blitz Cache.

## Table of Contents

- [Filter Hooks](#filter-hooks)
  - [Core Filters](#core-filters)
  - [Cache Filters](#cache-filters)
  - [Purge Filters](#purge-filters)
  - [Warmup Filters](#warmup-filters)
  - [Minify Filters](#minify-filters)
  - [Cloudflare Filters](#cloudflare-filters)
- [Action Hooks](#action-hooks)
  - [Cache Actions](#cache-actions)
  - [Purge Actions](#purge-actions)
  - [Warmup Actions](#warmup-actions)
  - [Cloudflare Actions](#cloudflare-actions)
  - [Lifecycle Actions](#lifecycle-actions)
- [Examples](#examples)

---

## Filter Hooks

### Core Filters

#### `blitz_cache_should_cache`

Determine if the current request should be cached.

**Parameters:**
- `bool $should_cache` - Whether to cache (default: true)

**Usage:**
```php
add_filter('blitz_cache_should_cache', function($should_cache) {
    // Don't cache specific pages
    if (is_page('special-page')) {
        return false;
    }
    return $should_cache;
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_ttl`

Modify the cache TTL (time to live) for specific URLs.

**Parameters:**
- `int $ttl` - Time to live in seconds
- `string $url` - The URL being cached

**Usage:**
```php
add_filter('blitz_cache_ttl', function($ttl, $url) {
    // Cache homepage for 1 hour
    if (is_front_page()) {
        return 3600; // 1 hour
    }
    return $ttl;
}, 10, 2);
```

**Priority:** 10
**Parameters:** 2

---

#### `blitz_cache_cache_query_strings`

Allow caching of URLs with query strings.

**Parameters:**
- `bool $cache` - Whether to cache query strings (default: false)

**Usage:**
```php
add_filter('blitz_cache_cache_query_strings', function($cache) {
    // Enable caching for query strings
    return true;
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_allowed_query_params`

Specify which query parameters don't bust the cache.

**Parameters:**
- `array $params` - Array of allowed query parameters

**Usage:**
```php
add_filter('blitz_cache_allowed_query_params', function($params) {
    // Add custom tracking parameters
    $params[] = 'utm_campaign';
    $params[] = 'ref';
    return $params;
});
```

**Priority:** 10
**Parameters:** 1

---

### Cache Filters

#### `blitz_cache_html_before_store`

Modify HTML before it's cached.

**Parameters:**
- `string $html` - The HTML to be cached

**Usage:**
```php
add_filter('blitz_cache_html_before_store', function($html) {
    // Add custom tracking code
    $tracking = '<script>/* Custom tracking */</script>';
    return $html . $tracking;
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_html_before_serve`

Modify HTML before serving from cache.

**Parameters:**
- `string $html` - The cached HTML

**Usage:**
```php
add_filter('blitz_cache_html_before_serve', function($html) {
    // Add dynamic content before serving
    return str_replace('{{dynamic}}', date('Y'), $html);
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_excluded_urls`

Add URLs to the exclusion list.

**Parameters:**
- `array $urls` - Array of URL patterns

**Usage:**
```php
add_filter('blitz_cache_excluded_urls', function($urls) {
    // Exclude API endpoints
    $urls[] = '/api/*';
    $urls[] = '/wp-json/*';
    return $urls;
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_excluded_cookies`

Add cookies to the exclusion list.

**Parameters:**
- `array $cookies` - Array of cookie name patterns

**Usage:**
```php
add_filter('blitz_cache_excluded_cookies', function($cookies) {
    // Exclude custom cookie
    $cookies[] = 'my_custom_cookie_*';
    return $cookies;
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_excluded_user_agents`

Add user agents to the exclusion list.

**Parameters:**
- `array $user_agents` - Array of user agent patterns

**Usage:**
```php
add_filter('blitz_cache_excluded_user_agents', function($user_agents) {
    // Exclude specific bots
    $user_agents[] = 'MyCustomBot/*';
    return $user_agents;
});
```

**Priority:** 10
**Parameters:** 1

---

### Purge Filters

#### `blitz_cache_purge_urls`

Modify the list of URLs to purge when content changes.

**Parameters:**
- `array $urls` - Array of URLs to purge
- `int $post_id` - The post ID that triggered the purge
- `WP_Post $post` - The post object

**Usage:**
```php
add_filter('blitz_cache_purge_urls', function($urls, $post_id, $post) {
    // Add custom URL to purge
    $urls[] = home_url('/custom-page/');
    return $urls;
}, 10, 3);
```

**Priority:** 10
**Parameters:** 3

---

### Warmup Filters

#### `blitz_cache_warmup_urls`

Modify the list of URLs to warm up (preload).

**Parameters:**
- `array $urls` - Array of URLs to warm up

**Usage:**
```php
add_filter('blitz_cache_warmup_urls', function($urls) {
    // Add custom pages to warmup
    $urls[] = home_url('/important-page/');
    $urls[] = home_url('/landing-page/');
    return $urls;
});
```

**Priority:** 10
**Parameters:** 1

---

#### `blitz_cache_custom_warmup_urls`

Provide custom URLs for cache warmup (when warmup source is 'custom').

**Parameters:**
- `array $urls` - Array of custom URLs

**Usage:**
```php
add_filter('blitz_cache_custom_warmup_urls', function() {
    return [
        home_url('/'),
        home_url('/about/'),
        home_url('/contact/'),
        home_url('/products/'),
    ];
});
```

**Priority:** 10
**Parameters:** 0

---

### Minify Filters

#### `blitz_cache_should_minify`

Control whether HTML should be minified.

**Parameters:**
- `bool $should_minify` - Whether to minify
- `string $html` - The HTML to minify

**Usage:**
```php
add_filter('blitz_cache_should_minify', function($should_minify, $html) {
    // Don't minify if HTML contains specific pattern
    if (strpos($html, 'no-minify') !== false) {
        return false;
    }
    return $should_minify;
}, 10, 2);
```

**Priority:** 10
**Parameters:** 2

---

### Cloudflare Filters

#### `blitz_cache_cf_purge_urls`

Modify URLs before purging from Cloudflare.

**Parameters:**
- `array $urls` - Array of URLs to purge
- `string $type` - Purge type ('all' or 'urls')

**Usage:**
```php
add_filter('blitz_cache_cf_purge_urls', function($urls, $type) {
    // Add custom URLs to Cloudflare purge
    if ($type === 'urls') {
        $urls[] = home_url('/custom-purge-url/');
    }
    return $urls;
}, 10, 2);
```

**Priority:** 10
**Parameters:** 2

---

## Action Hooks

### Cache Actions

#### `blitz_cache_after_store`

Fires after HTML is stored in cache.

**Parameters:**
- `string $key` - The cache key
- `string $html` - The cached HTML

**Usage:**
```php
add_action('blitz_cache_after_store', function($key, $html) {
    // Log caching event
    error_log("Cache stored: $key");
});
```

**Priority:** 10
**Parameters:** 2

---

#### `blitz_cache_hit`

Fires on cache hit (when serving cached content).

**Parameters:**
- None

**Usage:**
```php
add_action('blitz_cache_hit', function() {
    // Track cache hits
    $hits = get_option('cache_hits', 0);
    update_option('cache_hits', $hits + 1);
});
```

**Priority:** 10
**Parameters:** 0

---

#### `blitz_cache_miss`

Fires on cache miss (when content needs to be generated).

**Parameters:**
- None

**Usage:**
```php
add_action('blitz_cache_miss', function() {
    // Track cache misses
    $misses = get_option('cache_misses', 0);
    update_option('cache_misses', $misses + 1);
});
```

**Priority:** 10
**Parameters:** 0

---

### Purge Actions

#### `blitz_cache_after_purge`

Fires after all cache is purged.

**Parameters:**
- None

**Usage:**
```php
add_action('blitz_cache_after_purge', function() {
    // Clear other caches
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    // Send notification
    error_log('All cache purged');
});
```

**Priority:** 10
**Parameters:** 0

---

#### `blitz_cache_after_purge_url`

Fires after a specific URL is purged.

**Parameters:**
- `string $url` - The purged URL

**Usage:**
```php
add_action('blitz_cache_after_purge_url', function($url) {
    // Log purge event
    error_log("URL purged: $url");
});
```

**Priority:** 10
**Parameters:** 1

---

### Warmup Actions

#### `blitz_cache_after_warmup`

Fires after cache warmup completes.

**Parameters:**
- `array $urls` - Array of URLs that were warmed

**Usage:**
```php
add_action('blitz_cache_after_warmup', function($urls) {
    // Log warmup event
    error_log('Cache warmed: ' . count($urls) . ' URLs');
});
```

**Priority:** 10
**Parameters:** 1

---

### Cloudflare Actions

#### `blitz_cache_cf_purge_success`

Fires after successful Cloudflare purge.

**Parameters:**
- `string $type` - Purge type ('all' or 'urls')
- `array $urls` - Array of purged URLs (optional)

**Usage:**
```php
add_action('blitz_cache_cf_purge_success', function($type, $urls = []) {
    // Log successful purge
    error_log("Cloudflare purge success: $type");
});
```

**Priority:** 10
**Parameters:** 2

---

#### `blitz_cache_cf_purge_failed`

Fires after failed Cloudflare purge.

**Parameters:**
- `string $type` - Purge type ('all' or 'urls')
- `mixed $response` - Error response from Cloudflare API

**Usage:**
```php
add_action('blitz_cache_cf_purge_failed', function($type, $response) {
    // Log failed purge
    error_log("Cloudflare purge failed: $type - " . print_r($response, true));
});
```

**Priority:** 10
**Parameters:** 2

---

### Lifecycle Actions

#### `blitz_cache_activated`

Fires after plugin is activated.

**Parameters:**
- None

**Usage:**
```php
add_action('blitz_cache_activated', function() {
    // Run activation tasks
    flush_rewrite_rules();
});
```

**Priority:** 10
**Parameters:** 0

---

#### `blitz_cache_settings_saved`

Fires after settings are saved.

**Parameters:**
- `array $settings` - The saved settings

**Usage:**
```php
add_action('blitz_cache_settings_saved', function($settings) {
    // Respond to setting changes
    if (isset($settings['page_cache_enabled'])) {
        // Handle cache enable/disable
    }
});
```

**Priority:** 10
**Parameters:** 1

---

## Examples

### Complete Example: Conditional Caching

```php
/**
 * Cache only certain post types
 */
add_filter('blitz_cache_should_cache', function($should_cache) {
    // Only cache posts and pages
    if (is_singular(['post', 'page'])) {
        return true;
    }

    // Don't cache other post types
    if (is_singular()) {
        return false;
    }

    return $should_cache;
});
```

### Complete Example: Custom TTL

```php
/**
 * Set different TTL based on content type
 */
add_filter('blitz_cache_ttl', function($ttl, $url) {
    // Cache homepage for 2 hours
    if (is_front_page()) {
        return 7200; // 2 hours
    }

    // Cache static pages for 24 hours
    if (is_page()) {
        return 86400; // 24 hours
    }

    // Cache posts for 12 hours
    if (is_single()) {
        return 43200; // 12 hours
    }

    return $ttl;
}, 10, 2);
```

### Complete Example: Custom Warmup URLs

```php
/**
 * Provide custom warmup URLs
 */
add_filter('blitz_cache_custom_warmup_urls', function() {
    $urls = [
        home_url('/'),
        home_url('/about/'),
        home_url('/services/'),
        home_url('/contact/'),
    ];

    // Add all published pages
    $pages = get_pages();
    foreach ($pages as $page) {
        $urls[] = get_permalink($page->ID);
    }

    return $urls;
});
```

### Complete Example: Track Cache Performance

```php
/**
 * Track cache hit/miss ratio
 */
add_action('blitz_cache_hit', function() {
    $stats = get_option('blitz_cache_performance', [
        'hits' => 0,
        'misses' => 0
    ]);
    $stats['hits']++;
    update_option('blitz_cache_performance', $stats);
});

add_action('blitz_cache_miss', function() {
    $stats = get_option('blitz_cache_performance', [
        'hits' => 0,
        'misses' => 0
    ]);
    $stats['misses']++;
    update_option('blitz_cache_performance', $stats);
});

/**
 * Display cache performance in admin bar
 */
add_action('admin_bar_menu', function($wp_admin_bar) {
    $stats = get_option('blitz_cache_performance', [
        'hits' => 0,
        'misses' => 0
    ]);

    $total = $stats['hits'] + $stats['misses'];
    $ratio = $total > 0 ? round(($stats['hits'] / $total) * 100, 1) : 0;

    $wp_admin_bar->add_node([
        'id' => 'blitz-cache-stats',
        'title' => "Cache Hit Ratio: {$ratio}%",
        'href' => admin_url('admin.php?page=blitz-cache')
    ]);
}, 100);
```

---

## Best Practices

1. **Always return values in filters** - Filters must return the modified value
2. **Use appropriate priority** - Default priority is 10, use higher numbers for later execution
3. **Document your hooks** - Comment your custom hooks for clarity
4. **Check for existing filters** - Don't overwrite existing filters, use appropriate priority
5. **Validate input** - Always validate and sanitize input in your hooks
6. **Performance considerations** - Keep hook callbacks lightweight

---

## Hook Priority

Hooks are executed in order of priority (lower numbers run first). Default priority is 10.

```php
// This runs first (priority 5)
add_filter('blitz_cache_should_cache', 'first_callback', 5);

// This runs second (default priority 10)
add_filter('blitz_cache_should_cache', 'second_callback');

// This runs last (priority 20)
add_filter('blitz_cache_should_cache', 'third_callback', 20);
```

---

## Removing Hooks

You can remove hooks using `remove_filter()` or `remove_action()`:

```php
// Remove a specific filter
remove_filter('blitz_cache_should_cache', 'callback_function_name', 10);

// Remove a specific action
remove_action('blitz_cache_after_purge', 'callback_function_name', 10);
```

---

## Testing Hooks

Use PHPUnit to test your custom hooks:

```php
public function test_custom_cache_filter()
{
    // Add filter
    add_filter('blitz_cache_should_cache', function() {
        return false;
    });

    // Test
    $cache = new Blitz_Cache_Cache();
    $this->assertFalse($cache->should_cache());

    // Clean up
    remove_all_filters('blitz_cache_should_cache');
}
```

---

## Support

For questions about hooks:
- [GitHub Issues](https://github.com/BlitzCache/blitzcache/issues)
- [WordPress.org Forum](https://wordpress.org/support/plugin/blitz-cache)

---

*This documentation is for Blitz Cache v1.0.0*
