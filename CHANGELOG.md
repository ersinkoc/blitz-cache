# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-01-09

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

- **Test Suite**: 100% test coverage
  - Unit tests for all core classes
  - Integration tests for WooCommerce, EDD, LearnDash
  - Cloudflare API integration tests
  - Performance benchmarks
  - Automated CI/CD pipeline

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

---

## Roadmap

### [1.1.0] - Planned for Q2 2026

#### Added (Planned)
- Redis adapter for distributed caching
- Memcached adapter
- APCu adapter
- Cache statistics API
- Individual cache file deletion
- Selective cache purge (by tag/category)

#### Improved (Planned)
- Better mobile detection
- Enhanced sitemap parsing
- Improved WooCommerce integration
- More granular exclusions

### [1.2.0] - Planned for Q3 2026

#### Added (Planned)
- Multisite network-wide cache
- Cross-site cache sharing
- Network admin settings panel
- Site-specific cache management
- Bulk operations across network

#### Improved (Planned)
- Better multisite compatibility
- Improved cache sharing strategies

### [1.3.0] - Planned for Q4 2026

#### Added (Planned)
- AWS CloudFront integration
- KeyCDN integration
- MaxCDN integration
- Generic CDN adapter pattern
- Edge cache prewarming

#### Improved (Planned)
- Better CDN synchronization
- Improved edge cache invalidation

### [2.0.0] - Planned for 2027

#### Added (Planned)
- Object cache support
- Query result caching
- Fragment caching
- Advanced cache strategies (TTL by content type)
- Cache tagging system
- Intelligent cache warming (based on analytics)

#### Improved (Planned)
- Complete code architecture overhaul
- Better performance
- Enhanced developer experience

---

## Version History

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version (x.0.0): Incompatible API changes
- **MINOR** version (1.x.0): New functionality in a backwards-compatible manner
- **PATCH** version (1.0.x): Backwards-compatible bug fixes

### Release Schedule

- **Major Releases**: Once per year
- **Minor Releases**: Every 3-4 months
- **Patch Releases**: As needed (security, critical bugs)

### Support Policy

- **Current Version**: Full support (features, bugs, security)
- **Previous Major**: Security updates only
- **Older Versions**: No support (please upgrade)

### End of Life

Versions reach end of life according to this schedule:
- v1.x: Supported until v2.0 release
- v1.0: End of life with v1.2.0 release

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on:
- Bug reports
- Feature requests
- Pull requests
- Code standards
- Testing requirements

---

## Changelog Types

- **Added**: for new features
- **Changed**: for changes in existing functionality
- **Deprecated**: for soon-to-be removed features
- **Removed**: for now removed features
- **Fixed**: for any bug fixes
- **Security**: for security vulnerabilities
- **Improved**: for performance improvements
- **Optimized**: for code optimizations
- **Refactored**: for code refactoring

---

## Links

- **Repository**: https://github.com/ersinkoc/blitz-cache
- **Documentation**: https://github.com/ersinkoc/blitz-cache/tree/main/docs
- **Issues**: https://github.com/ersinkoc/blitz-cache/issues
- **Discussions**: https://github.com/ersinkoc/blitz-cache/discussions

---

**Maintained by [Ersin KOÇ](https://github.com/ersinkoc)**

*This changelog was generated on 2026-01-09*
