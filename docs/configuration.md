# Blitz Cache Configuration Guide

This guide explains all configuration options available in Blitz Cache.

## Table of Contents

- [Accessing Settings](#accessing-settings)
- [Page Cache Settings](#page-cache-settings)
- [Browser Cache Settings](#browser-cache-settings)
- [Compression Settings](#compression-settings)
- [Exclusions](#exclusions)
- [Preload (Cache Warmup)](#preload-cache-warmup)
- [Cloudflare Settings](#cloudflare-settings)
- [Update Channels](#update-channels)
- [Advanced Configuration](#advanced-configuration)
- [Recommended Settings](#recommended-settings)

---

## Accessing Settings

### From WordPress Admin

1. Log in to your WordPress admin dashboard
2. Click **Blitz Cache** in the left menu
3. You'll see 4 tabs:
   - **Dashboard** - View statistics and quick actions
   - **Settings** - Configure caching behavior
   - **Cloudflare** - Manage Cloudflare integration
   - **Tools** - Export/import settings and debug info

### Via WP-CLI

```bash
# View current settings
wp option get blitz_cache_settings

# Update a specific setting
wp option update blitz_cache_settings '{"page_cache_enabled": true}'

# Get Cloudflare settings
wp option get blitz_cache_cloudflare
```

---

## Page Cache Settings

Configure how Blitz Cache handles HTML page caching.

### Enable Page Cache

**Default:** Enabled ✅

Controls whether Blitz Cache should cache HTML pages.

**When to disable:**
- During development
- Testing dynamic content
- Debugging issues

**Command Line:**
```php
add_filter('blitz_cache_should_cache', function($cache) {
    return true; // or false
});
```

---

### Cache TTL (Time To Live)

**Default:** 86400 seconds (24 hours)

How long to keep cached pages before regenerating.

#### Recommended TTL Values

| Page Type | TTL | Use Case |
|-----------|-----|----------|
| Homepage | 7200-21600 (2-6 hours) | Updates frequently |
| Blog Posts | 43200-86400 (12-24 hours) | Updates daily |
| Static Pages | 86400-259200 (1-3 days) | Rarely changes |
| Product Pages | 86400 (24 hours) | Updates daily |
| Landing Pages | 604800 (7 days) | Rarely changes |

#### Setting TTL

**Via Admin:**
`Blitz Cache > Settings > Page Cache > Cache TTL`

**Via Code:**
```php
add_filter('blitz_cache_ttl', function($ttl, $url) {
    if (is_front_page()) {
        return 3600; // 1 hour for homepage
    }
    if (is_page()) {
        return 86400; // 24 hours for static pages
    }
    return $ttl;
}, 10, 2);
```

---

### Cache Logged-in Users

**Default:** Disabled ❌

Enable caching for users who are logged in.

**⚠️ Warning:** Enabling this may serve personalized content to other users.

**When to enable:**
- Membership sites with public content
- Sites where all users see same content
- Sites with user dashboards but cacheable public areas

**Via Admin:**
`Blitz Cache > Settings > Page Cache > Cache Logged-in Users`

**Via Code:**
```php
add_filter('blitz_cache_should_cache', function($should_cache) {
    if (is_user_logged_in()) {
        // Only cache if viewing public content
        return !is_admin() && !is_page('account');
    }
    return $should_cache;
});
```

---

### Separate Mobile Cache

**Default:** Disabled ❌

Create separate cache for mobile devices.

**When to enable:**
- Site has different content for mobile
- Mobile and desktop versions differ significantly
- Using responsive design with mobile-specific features

**Via Admin:**
`Blitz Cache > Settings > Page Cache > Separate Mobile Cache`

**Via Code:**
```php
// Enable/disable based on device type
add_filter('blitz_cache_should_cache', function($should_cache) {
    if (wp_is_mobile()) {
        // Different logic for mobile
        return $should_cache;
    }
    return $should_cache;
});
```

---

## Browser Cache Settings

Configure how browsers cache static assets (CSS, JS, images).

### Enable Browser Cache

**Default:** Enabled ✅

Set cache headers for browsers to cache static resources.

**What it does:**
- Sets `Cache-Control` headers
- Improves return visitor experience
- Reduces server load

**Note:** These headers are sent via `.htaccess` rules, not PHP.

---

### CSS/JS TTL

**Default:** 2592000 seconds (30 days)

How long browsers should cache CSS and JavaScript files.

**When to change:**
- **Increase** (60-90 days) if CSS/JS rarely changes
- **Decrease** (7-14 days) if updating frequently

**Via Code:**
```apache
# Add to .htaccess
<filesMatch "\.(css|js)$">
    Header set Cache-Control "max-age=2592000, public"
</filesMatch>
```

---

### Images TTL

**Default:** 7776000 seconds (90 days)

How long browsers should cache image files.

**When to change:**
- **Increase** (180-365 days) for site logos and static images
- **Decrease** (30-60 days) for frequently changing images

**Via Code:**
```apache
# Add to .htaccess
<filesMatch "\.(jpg|jpeg|png|gif|webp|svg)$">
    Header set Cache-Control "max-age=7776000, public"
</filesMatch>
```

---

## Compression Settings

### Enable GZIP

**Default:** Enabled ✅

Compress cached HTML files with GZIP to reduce bandwidth.

**Benefits:**
- Up to 80% smaller file sizes
- Faster page loads
- Reduced bandwidth costs

**When to disable:**
- Server already compresses via Nginx/Apache
- Very low traffic sites

**Verification:**
Check if GZIP is working:
```bash
curl -H "Accept-Encoding: gzip" -I https://yoursite.com
```

Should return:
```
Content-Encoding: gzip
```

---

### Enable HTML Minify

**Default:** Enabled ✅

Remove unnecessary whitespace from cached HTML.

**What it does:**
- Removes extra spaces and newlines
- Preserves inline scripts and styles
- Reduces file size by 5-15%

**When to disable:**
- Using plugins that modify HTML output
- Displaying pre-formatted content

**Via Code:**
```php
add_filter('blitz_cache_should_minify', function($should_minify, $html) {
    // Don't minify specific pages
    if (is_page('special-page')) {
        return false;
    }
    return $should_minify;
}, 10, 2);
```

---

## Exclusions

Specify what should NOT be cached.

### Excluded URLs

**Default:** Empty

URL patterns to exclude from caching.

**Common Patterns:**

| Pattern | Example | Matches |
|---------|---------|---------|
| `/cart/*` | `/cart/`, `/cart/item/123` | All cart pages |
| `*checkout*` | `/checkout`, `/my-checkout` | Pages with "checkout" |
| `/wp-admin/*` | `/wp-admin/` | All admin pages |
| `/api/*` | `/api/users`, `/api/posts` | All API endpoints |
| `?utm_*` | `?utm_source=google` | URLs with UTM parameters |

**Via Admin:**
`Blitz Cache > Settings > Exclusions > Excluded URLs`

**Via Code:**
```php
add_filter('blitz_cache_excluded_urls', function($urls) {
    $urls[] = '/my-custom-page/*';
    $urls[] = '*/special-event/*';
    return $urls;
});
```

---

### Excluded Cookies

**Default:**
- `wordpress_logged_in_*`
- `woocommerce_cart_hash`
- `woocommerce_items_in_cart`

Cookie names that prevent caching when present.

**Common Patterns:**

| Pattern | Matches | Use Case |
|---------|---------|----------|
| `wordpress_logged_in_*` | `wordpress_logged_in_123` | WordPress login cookie |
| `woocommerce_cart_*` | `woocommerce_cart_hash` | WooCommerce cart cookies |
| `*session*` | `session_id`, `my_session` | Session cookies |
| `*auth*` | `auth_token`, `auth_token_*` | Authentication cookies |

**Via Admin:**
`Blitz Cache > Settings > Exclusions > Excluded Cookies`

**Via Code:**
```php
add_filter('blitz_cache_excluded_cookies', function($cookies) {
    $cookies[] = 'my_custom_cookie_*';
    return $cookies;
});
```

---

### Excluded User Agents

**Default:** Empty

User agent strings that should not receive cached pages.

**Common Patterns:**

| Pattern | Matches | Use Case |
|---------|---------|----------|
| `bot` | `Googlebot`, `bingbot` | Search engine bots |
| `spider` | Any spider | Crawlers |
| `crawler` | Any crawler | Crawlers |
| `*bot/*` | Specific bots | Targeted exclusion |

**⚠️ Warning:** Be careful excluding user agents. Bots should be excluded to avoid wasting resources.

**Via Admin:**
`Blitz Cache > Settings > Exclusions > Excluded User Agents`

**Via Code:**
```php
add_filter('blitz_cache_excluded_user_agents', function($agents) {
    $agents[] = 'MyCustomBot/*';
    return $agents;
});
```

---

## Preload (Cache Warmup)

Automatically refill cache after purging.

### Enable Cache Warmup

**Default:** Enabled ✅

Automatically visit and cache pages after purging.

**Benefits:**
- Always fresh cache
- Better user experience
- No cold cache after updates

---

### Warmup Source

**Default:** Sitemap

Where to get URLs for cache warmup.

#### Option 1: Sitemap

**Recommended for most sites.**

Blitz Cache will:
1. Read your XML sitemap (`/wp-sitemap.xml` or `/sitemap_index.xml`)
2. Parse all URLs from sitemap
3. Visit each URL to cache it

**Supported Sitemaps:**
- WordPress native sitemap (WP 5.5+)
- Yoast SEO sitemap
- RankMath sitemap
- Other XML sitemaps

**Via Code:**
```php
// Custom sitemap URL
add_filter('blitz_cache_custom_warmup_urls', function() {
    $sitemap_url = home_url('/custom-sitemap.xml');
    return $this->parse_sitemap($sitemap_url);
});
```

---

#### Option 2: Navigation Menu

**Good for content-focused sites.**

Blitz Cache will:
1. Get all registered menu locations
2. Extract URLs from menu items
3. Cache those pages

**Via Code:**
```php
// Only use specific menu
add_filter('blitz_cache_warmup_urls', function($urls) {
    $menu_items = wp_get_nav_menu_items('primary-menu');
    foreach ($menu_items as $item) {
        $urls[] = $item->url;
    }
    return array_unique($urls);
});
```

---

#### Option 3: Custom

**For advanced users.**

Provide URLs via the `blitz_cache_custom_warmup_urls` filter.

**Via Code:**
```php
add_filter('blitz_cache_custom_warmup_urls', function() {
    return [
        home_url('/'),
        home_url('/about/'),
        home_url('/services/'),
        home_url('/contact/'),
    ];
});
```

---

### Warmup Interval

**Default:** 21600 seconds (6 hours)

How often to automatically warm up the cache.

**Options:**
- 7200 seconds (2 hours) - High traffic, frequently updated sites
- 21600 seconds (6 hours) - Default for most sites
- 43200 seconds (12 hours) - Low traffic sites
- 86400 seconds (24 hours) - Static sites

**Via Code:**
```php
// Schedule based on content type
add_filter('blitz_cache_ttl', function($ttl, $url) {
    if (is_front_page()) {
        return 3600; // Warm homepage every hour
    }
    return $ttl;
}, 10, 2);
```

---

### Warmup Batch Size

**Default:** 5 URLs per batch

How many URLs to process in each batch during warmup.

**Why batches?**
- Prevents server overload
- Spreads CPU usage over time
- Better for shared hosting

**When to adjust:**

| Site Size | Batch Size | Reason |
|-----------|-----------|--------|
| Small (< 100 pages) | 3-5 | Gentle on server |
| Medium (100-500 pages) | 5-10 | Balanced approach |
| Large (500+ pages) | 10-20 | Faster completion |
| Very Large (1000+ pages) | 20-50 | Requires powerful server |

**Via Code:**
```php
// Adjust based on site size
add_filter('blitz_cache_warmup_urls', function($urls) {
    // Process in smaller batches for large sites
    return array_slice($urls, 0, 100); // Only warm first 100 URLs
});
```

---

## Cloudflare Settings

Configure Cloudflare integration.

**See:** [Cloudflare Setup Guide](cloudflare.md)

---

## Update Channels

Choose where Blitz Cache checks for updates.

### Option 1: Stable (WordPress.org)

**Default for most users.**

- Updates from WordPress.org repository
- Thoroughly tested
- Slower release cycle
- Best for production sites

---

### Option 2: Stable (GitHub)

- Latest stable releases from GitHub
- Faster than WordPress.org
- Good for testing

---

### Option 3: Beta (GitHub)

- Access to beta versions
- Latest features
- May have bugs
- For testing only

**⚠️ Warning:** Don't use beta versions on production sites.

---

## Advanced Configuration

### Cache Key Customization

By default, cache keys are MD5 hashes of URLs. You can customize:

```php
add_filter('blitz_cache_cache_key', function($key, $url) {
    // Add additional data to cache key
    $user_group = is_user_logged_in() ? 'logged-in' : 'guest';
    $device = wp_is_mobile() ? 'mobile' : 'desktop';
    return md5($url . $user_group . $device);
}, 10, 2);
```

---

### Dynamic Cache Invalidation

Force cache purge when specific events occur:

```php
// Purge cache when specific post meta changes
add_action('updated_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'featured_product') {
        $purge = new Blitz_Cache_Purge();
        $purge->purge_url(get_permalink($post_id));
    }
}, 10, 4);
```

---

### Conditional Caching Logic

```php
add_filter('blitz_cache_should_cache', function($should_cache) {
    // Don't cache if visitor has a special cookie
    if (isset($_COOKIE['special_visitor'])) {
        return false;
    }

    // Don't cache specific post types
    if (is_singular('custom_post_type')) {
        return false;
    }

    // Only cache during specific hours
    $hour = date('H');
    if ($hour < 8 || $hour > 20) {
        return false;
    }

    return $should_cache;
});
```

---

### Custom TTL Per Content Type

```php
add_filter('blitz_cache_ttl', function($ttl, $url) {
    if (is_singular('news')) {
        return 3600; // News articles expire in 1 hour
    }

    if (is_singular('documentation')) {
        return 604800; // Docs cached for 1 week
    }

    if (is_post_type_archive('product')) {
        return 86400; // Product archives cached for 1 day
    }

    return $ttl;
}, 10, 2);
```

---

## Recommended Settings

### Small Business Website (< 100 pages)

```php
[
    'page_cache_enabled' => true,
    'page_cache_ttl' => 86400, // 24 hours
    'cache_logged_in' => false,
    'mobile_cache' => false,
    'browser_cache_enabled' => true,
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    'warmup_enabled' => true,
    'warmup_source' => 'sitemap',
    'warmup_interval' => 21600, // 6 hours
    'warmup_batch_size' => 5,
]
```

---

### Blog (< 500 posts)

```php
[
    'page_cache_enabled' => true,
    'page_cache_ttl' => 43200, // 12 hours
    'cache_logged_in' => false,
    'mobile_cache' => false,
    'browser_cache_enabled' => true,
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    'warmup_enabled' => true,
    'warmup_source' => 'sitemap',
    'warmup_interval' => 21600, // 6 hours
    'warmup_batch_size' => 10,
]
```

---

### E-commerce (WooCommerce)

```php
[
    'page_cache_enabled' => true,
    'page_cache_ttl' => 86400, // 24 hours
    'cache_logged_in' => false,
    'mobile_cache' => true, // Product pages often differ
    'browser_cache_enabled' => true,
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    'warmup_enabled' => true,
    'warmup_source' => 'sitemap',
    'warmup_interval' => 43200, // 12 hours
    'warmup_batch_size' => 10,
    // WooCommerce exclusions are automatic
]
```

---

### Membership Site

```php
[
    'page_cache_enabled' => true,
    'page_cache_ttl' => 86400, // 24 hours
    'cache_logged_in' => true, // Cache public content for members
    'mobile_cache' => false,
    'browser_cache_enabled' => true,
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    'warmup_enabled' => true,
    'warmup_source' => 'menu', // Use navigation
    'warmup_interval' => 86400, // 24 hours
    'warmup_batch_size' => 5,
]
```

---

### News Site (High Traffic)

```php
[
    'page_cache_enabled' => true,
    'page_cache_ttl' => 3600, // 1 hour
    'cache_logged_in' => false,
    'mobile_cache' => true,
    'browser_cache_enabled' => true,
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    'warmup_enabled' => true,
    'warmup_source' => 'sitemap',
    'warmup_interval' => 3600, // 1 hour
    'warmup_batch_size' => 20,
]
```

---

### Static Business Site (Rare Updates)

```php
[
    'page_cache_enabled' => true,
    'page_cache_ttl' => 604800, // 1 week
    'cache_logged_in' => false,
    'mobile_cache' => false,
    'browser_cache_enabled' => true,
    'css_js_ttl' => 2592000, // 30 days
    'images_ttl' => 7776000, // 90 days
    'gzip_enabled' => true,
    'html_minify_enabled' => true,
    'warmup_enabled' => true,
    'warmup_source' => 'menu',
    'warmup_interval' => 86400, // 24 hours
    'warmup_batch_size' => 5,
]
```

---

## Best Practices

### 1. Start with Defaults

Blitz Cache is configured with smart defaults that work for most sites. Don't over-configure initially.

### 2. Monitor Hit Ratio

Check cache hit ratio in `Blitz Cache > Dashboard`.

**Good:** 90%+
**Needs Improvement:** 70-90%
**Poor:** < 70%

**To improve hit ratio:**
- Increase TTL
- Reduce exclusions
- Check for conflicts

### 3. Test After Changes

After changing settings:
1. Clear cache
2. Visit your site
3. Check for errors
4. Monitor hit ratio

### 4. Use Staging Site

Test configuration changes on a staging site before applying to production.

### 5. Regular Reviews

Review settings monthly:
- Is TTL appropriate for your update frequency?
- Are exclusions correct?
- Is warmup working properly?

---

## Configuration Checklist

- [ ] Page cache enabled
- [ ] TTL set appropriately
- [ ] GZIP enabled (unless server does it)
- [ ] HTML minify enabled (unless conflicts)
- [ ] Exclusions configured (cart, checkout, etc.)
- [ ] Browser cache headers enabled
- [ ] Cache warmup enabled (recommended)
- [ ] Warmup source configured
- [ ] Cloudflare integration set up (if using CF)
- [ ] Test cache is working
- [ ] Monitor hit ratio

---

## Getting Help

### Documentation
- [Installation Guide](installation.md)
- [Cloudflare Setup](cloudflare.md)
- [Hooks Reference](HOOKS.md)
- [Troubleshooting](troubleshooting.md)

### Support
- [GitHub Issues](https://github.com/BlitzCache/blitzcache/issues)
- [WordPress.org Forum](https://wordpress.org/support/plugin/blitz-cache)

---

**Happy caching! 🚀**
