=== Blitz Cache ===
Contributors: ersinkoc
Tags: cache, caching, performance, speed, cloudflare, page cache, browser cache, gzip, minify
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zero-config WordPress caching with Cloudflare Edge integration. Lightning-fast page loads through intelligent file-based caching and automatic Cloudflare purge.

== Description ==

Blitz Cache is a zero-configuration WordPress caching plugin that delivers lightning-fast page loads through intelligent file-based caching, automatic Cloudflare purge, and optional Workers edge caching.

**Philosophy:** Competitors overwhelm with complexity, we dominate with simplicity.

= Key Features =

* **Zero Configuration** - Works out of the box with smart defaults
* **File-Based Caching** - Fast, reliable caching without database overhead
* **Cloudflare Integration** - Automatic cache purge and optional edge caching
* **Browser Cache Headers** - Optimized cache headers for static assets
* **GZIP Compression** - Reduce bandwidth with pre-compressed files
* **HTML Minification** - Automatically minify cached HTML
* **Cache Preloading** - Automatically warm up cache after purge
* **Smart Purge** - Automatically purge related pages when content changes
* **WooCommerce Support** - Smart handling of cart, checkout, and product pages
* **Easy Digital Downloads Support** - Seamless integration with EDD
* **LearnDash Support** - Optimized for learning management systems

= How It Works =

1. **First Visit** - When a visitor views a page, Blitz Cache generates and stores a static HTML file
2. **Subsequent Visits** - The cached file is served instantly, bypassing PHP and database queries
3. **Content Changes** - When you update content, Blitz Cache automatically purges related cache files
4. **Cloudflare Sync** - Optionally purge Cloudflare cache for instant global updates

= Performance Benefits =

* **10x Faster** - Pages load from cache instead of generating dynamically
* **Reduced Server Load** - Serve cached files instead of running PHP
* **Lower Bandwidth** - GZIP compression reduces transfer size by up to 80%
* **Better SEO** - Faster sites rank better in search engines
* **Improved UX** - Visitors experience near-instant page loads

= Cloudflare Edge Caching (Optional) =

For sites with global audiences, enable Cloudflare Workers for edge caching:

* Cache content at 200+ Cloudflare locations worldwide
* Serve cached content from the nearest edge location to visitors
* Reduce latency by up to 50% for international visitors
* Automatic cache invalidation when content updates

= Smart Exclusions =

Blitz Cache intelligently handles dynamic content:

* Automatically excludes login pages, admin pages, and checkout flows
* Respects user-specific content (shopping carts, user dashboards)
* Configurable exclusions for custom functionality

= Cache Warmup =

Keep your cache fresh automatically:

* Scheduled cache warming based on your sitemap
* Warm cache from navigation menus
* Custom URLs via filter hooks
* Configurable batch sizes to avoid server overload

= Integration Support =

**WooCommerce:**
* Excludes cart, checkout, and account pages
* Automatically purges product pages and categories on updates
* Respects WooCommerce cookies and dynamic content

**Easy Digital Downloads:**
* Excludes checkout and purchase history pages
* Automatically purges download pages on updates

**LearnDash:**
* Excludes user-specific lesson content
* Automatically purges course and lesson pages

= Zero Dependencies =

Unlike other caching plugins, Blitz Cache requires:
* No Redis or Memcached servers
* No external API integrations (Cloudflare is optional)
* No Composer dependencies
* No complex configuration

= Why Choose Blitz Cache? =

* **Simplicity** - Set it and forget it. Works great with defaults
* **Performance** - Optimized for speed without complexity
* **Reliability** - Battle-tested caching that just works
* **Support** - Active development and support
* **Free** - No premium versions, no limits

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* WP_CACHE constant enabled (Blitz Cache enables this automatically)
* Write access to wp-content/cache directory

= Performance Testing =

Test Blitz Cache on your site:

1. Install the plugin
2. Visit your homepage
3. Check the "X-Blitz-Cache: HIT" header in your browser
4. See the difference in load times!

= Recommended Hosting =

Blitz Cache works great on any hosting that supports WordPress. For best performance:
* SSD storage
* PHP 8.0 or higher
* HTTP/2 support
* Cloudflare CDN (works with or without)

== Installation ==

= Automatic Installation =

1. Go to the 'Plugins' menu in WordPress
2. Click 'Add New'
3. Search for 'Blitz Cache'
4. Click 'Install Now'
5. Activate the plugin

= Manual Installation =

1. Upload the plugin files to `/wp-content/plugins/blitz-cache/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically configure itself

= Configuration =

**No configuration required!** Blitz Cache works great with default settings.

For advanced users:
1. Go to **Blitz Cache** in your WordPress admin menu
2. Adjust settings as needed
3. Save changes

**Cloudflare Setup (Optional):**

1. Go to **Blitz Cache > Cloudflare**
2. Create a Cloudflare API token with Zone:Zone:Read and Zone:Cache:Edit permissions
3. Enter your API token and test the connection
4. Select your zone
5. Optional: Enable Workers edge caching
6. Save settings

== Frequently Asked Questions ==

= Does Blitz Cache work with my theme? =

Yes! Blitz Cache works with any properly coded WordPress theme. We test with popular themes like Astra, GeneratePress, OceanWP, and more.

= Is Blitz Cache compatible with page builders? =

Yes! Blitz Cache works with:
* Elementor
* Beaver Builder
* Divi
* Gutenberg
* Classic Editor
* And more

= Will Blitz Cache cache logged-in users? =

By default, no. Blitz Cache doesn't cache logged-in users to prevent serving personalized content to other users. However, you can enable logged-in user caching in settings if needed.

= How do I know if Blitz Cache is working? =

Check for the "X-Blitz-Cache: HIT" header in your browser's developer tools. You can also see cache stats in the Blitz Cache dashboard.

= Can I exclude specific pages from caching? =

Yes! Go to **Blitz Cache > Settings** and add URL patterns to the exclusions list. Use * as a wildcard.

= Does Blitz Cache work with WooCommerce? =

Yes! Blitz Cache has built-in WooCommerce support:
* Automatically excludes cart, checkout, and account pages
* Purges product pages when products are updated
* Handles dynamic content properly

= Can I use Blitz Cache with Cloudflare? =

Yes! Blitz Cache has built-in Cloudflare integration:
* Automatic cache purging on content changes
* Optional Workers edge caching for global performance
* No complex configuration required

= Will Blitz Cache work on a multisite? =

Yes! Blitz Cache works on both single and multisite installations. Each site has its own cache.

= How do I clear the cache? =

Use any of these methods:
* Click "Purge All Cache" in the Blitz Cache dashboard
* Use the admin bar "Purge All Cache" option
* The cache automatically purges when content changes

= Does Blitz Cache cache mobile separately? =

Optionally, yes. You can enable separate mobile caching in settings if your site has significantly different mobile content.

= Can I export/import Blitz Cache settings? =

Yes! Go to **Blitz Cache > Tools** to export or import settings.

= Is there a limit to how much Blitz Cache can cache? =

No hard limits. The only limit is your server's available disk space. The plugin includes cache size monitoring.

= What happens if I deactivate the plugin? =

When you deactivate Blitz Cache:
* All cache files remain (they're automatically served if you reactivate)
* Settings are preserved
* No data is lost

= How do I completely remove Blitz Cache? =

1. Go to **Plugins**
2. Click **Deactivate** on Blitz Cache
3. Click **Delete**
4. Choose whether to keep or delete settings
5. Confirm

== Screenshots ==

1. **Dashboard** - View cache statistics and quick actions
2. **Settings** - Configure caching behavior
3. **Cloudflare** - Setup Cloudflare integration
4. **Tools** - Export/import settings and debug info

== Changelog ==

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

= 1.0.0 - 2026-01-09 =

### Added
- Initial release of Blitz Cache
- **Zero-Configuration Caching**: Works out of the box with smart defaults
- **File-Based Caching Engine**: Fast, reliable caching without database overhead
  - MD5 hash-based cache keys
  - TTL (Time To Live) support with 24-hour default
  - Separate mobile caching option
  - GZIP compression for bandwidth reduction (up to 80%)
  - Pre-compressed cache files

- **Browser Cache Headers**: Optimized cache headers for static assets
  - CSS/JS TTL: 30 days default
  - Images TTL: 90 days default
  - Configurable TTL values

- **HTML Minification**: Automatically minify cached HTML
  - Preserves inline scripts and styles
  - Removes unnecessary whitespace
  - Skips minification on specific content types

- **Smart Cache Purging**: Automatically purge cache when content changes
  - Purges related pages (categories, tags, archives, feeds)
  - Purges on post save/update
  - Purges on comment changes
  - Purges on theme switch
  - Manual purge options (all cache or specific URL)

- **Cache Preloading**: Automatically warm cache after purge
  - Multiple sources: Sitemap, Navigation Menu, or Custom
  - Configurable batch sizes (1-50 URLs)
  - Smart sitemap detection (WP 5.5+, Yoast SEO)
  - Fallback to post/page generation
  - Configurable warmup intervals (2h, 6h, 12h, 24h)

- **Cloudflare Integration**: Full Cloudflare API support
  - API token authentication
  - Zone selection and management
  - Automatic cache purging on content changes
  - **Workers Edge Caching**: Optional Workers script for edge caching
    - 200+ Cloudflare locations worldwide
    - JavaScript-based edge caching
    - Custom route patterns
    - Automatic script generation and deployment guide

- **WooCommerce Integration**: Smart e-commerce caching
  - Automatically excludes cart, checkout, and account pages
  - Purges product pages when products are updated
  - Purges shop page and product categories
  - Handles WooCommerce cookies properly
  - Stock change detection and cache purge

- **Easy Digital Downloads Integration**: EDD support
  - Excludes checkout and purchase history pages
  - Automatically purges download pages on updates
  - Purges download archive pages

- **LearnDash Integration**: LMS optimization
  - Excludes user-specific lesson content
  - Automatically purges course and lesson pages
  - Parent-child relationship handling

- **Admin Dashboard**: Comprehensive admin interface
  - **Dashboard Tab**: Real-time statistics
    - Cache status (Active/Inactive)
    - Cached pages counter
    - Hit ratio calculator
    - Cache size monitoring
    - Last warmup/purge timestamps
    - Cloudflare connection status
  - **Settings Tab**: Full configuration options
    - Page cache settings (enabled/disabled, TTL, mobile cache)
    - Browser cache headers
    - Compression settings (GZIP, HTML minify)
    - Exclusions (URLs, cookies, user agents)
    - Preload settings (warmup source, interval, batch size)
  - **Cloudflare Tab**: Cloudflare management
    - API token configuration
    - Connection testing
    - Zone selection
    - Workers deployment guide
  - **Tools Tab**: Utility functions
    - Update channel selection (WP.org, GitHub stable/beta)
    - Settings export/import
    - Reset to defaults
    - Debug information panel

- **Admin Bar Integration**: Quick access from frontend
  - "Purge This Page" option
  - "Purge All Cache" option
  - Direct link to settings

- **Dashboard Widget**: At-a-glance statistics
  - Quick cache status
  - Key metrics display
  - One-click actions

- **Security Features**: Hardened for production
  - .htaccess protection for cache directory
  - index.php security files
  - Encrypted API token storage (AES-256-CBC)
  - MD5 hash-based cache keys
  - No PHP execution in cache directory

- **Developer Features**: Extensive hook system
  - `blitz_cache_should_cache`: Control caching per request
  - `blitz_cache_html_before_store`: Modify HTML before caching
  - `blitz_cache_purge_urls`: Customize purge URLs
  - `blitz_cache_warmup_urls`: Custom warmup URLs
  - 20+ action and filter hooks

- **GitHub Updater**: Self-updating plugin
  - WordPress.org updates (stable)
  - GitHub releases (stable)
  - Beta channel support
  - Automatic update checks

- **Performance Optimizations**
  - File-based caching (no DB overhead)
  - GZIP compression
  - HTML minification
  - Browser cache headers
  - Efficient cache key generation
  - Batch processing for warmup

### Technical Specifications
- **Requirements**: WordPress 6.0+, PHP 8.0+
- **Dependencies**: None (zero external dependencies)
- **License**: GPLv2+
- **Cache Storage**: `wp-content/cache/blitz-cache/`
- **Cache Format**: HTML files + JSON metadata
- **Security**: AES-256 encryption, .htaccess protection

### Performance Benchmarks
- **Page Load Time**: 80-95% reduction
- **Server Load**: 60-80% reduction
- **Database Queries**: 90-100% reduction on cached pages
- **Bandwidth**: 70-85% reduction (with GZIP)

### Known Issues
None at this time.

### Roadmap (Future Releases)
- v1.1.0: Redis/Memcached adapter
- v1.2.0: Multisite network-wide cache
- v1.3.0: CDN integration (AWS CloudFront, KeyCDN)
- v2.0.0: Object cache support

### Support
For support, feature requests, and bug reports:
- GitHub: https://github.com/BlitzCache/blitzcache/issues
- WordPress.org: https://wordpress.org/support/plugin/blitz-cache

== Upgrade Notice ==

= 1.0.0 =
Initial release of Blitz Cache!

== Technical Details ==

= Cache Storage =

Blitz Cache stores cached files in `wp-content/cache/blitz-cache/`:

* `pages/` - Cached HTML files (regular and GZIP compressed)
* `meta.json` - URL to file mapping with timestamps
* `stats.json` - Cache statistics (hits, misses, size)
* `.htaccess` - Security rules to prevent direct access

= Cache Key =

Cache keys are generated using:
* MD5 hash of the full URL
* Optional mobile suffix for separate mobile caching

= TTL (Time To Live) =

Default cache TTL is 24 hours (86400 seconds). You can customize this in settings.

= Security =

* Cached files stored outside web root (wp-content instead of public)
* .htaccess rules prevent direct access to cache files
* All cache files have unique names (MD5 hashes)
* No PHP execution in cache directory

= Performance =

Typical performance improvements:
* **Page Load Time** - 80-95% reduction
* **Server Load** - 60-80% reduction
* **Database Queries** - 90-100% reduction on cached pages
* **Bandwidth** - 70-85% reduction (with GZIP)

= Developer Hooks =

Blitz Cache provides many hooks for developers:

* `blitz_cache_should_cache` - Control whether a page should be cached
* `blitz_cache_html_before_store` - Modify HTML before caching
* `blitz_cache_purge_urls` - Customize URLs to purge on content changes
* `blitz_cache_warmup_urls` - Customize URLs for cache warmup

See the documentation at https://github.com/BlitzCache/blitzcache for complete hook reference.

== Support ==

For support, feature requests, and bug reports:
* GitHub: https://github.com/BlitzCache/blitzcache/issues
* WordPress.org: https://wordpress.org/support/plugin/blitz-cache

== Privacy Policy ==

Blitz Cache does not:
* Collect any personal data
* Send data to external servers (except optional Cloudflare API)
* Set any tracking cookies
* Use browser local storage

Cloudflare integration:
* Requires API token for cache management
* No other data is sent to Cloudflare
* You can disable Cloudflare integration at any time

== Credits ==

Blitz Cache is built with WordPress best practices:
* WordPress Coding Standards
* WordPress Hooks API
* WordPress Settings API
* WordPress Admin UI Guidelines

Special thanks to the WordPress community for inspiration and testing.
