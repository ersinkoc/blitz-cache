# Blitz Cache Installation Guide

This guide will walk you through installing Blitz Cache on your WordPress site.

## Table of Contents

- [Requirements](#requirements)
- [Installation Methods](#installation-methods)
  - [Method 1: WordPress Admin (Recommended)](#method-1-wordpress-admin-recommended)
  - [Method 2: WP-CLI](#method-2-wp-cli)
  - [Method 3: Manual Upload](#method-3-manual-upload)
  - [Method 4: GitHub](#method-4-github)
- [Post-Installation](#post-installation)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)
- [Uninstallation](#uninstallation)

---

## Requirements

Before installing Blitz Cache, ensure your system meets these requirements:

### Server Requirements

| Requirement | Minimum | Recommended |
|------------|---------|-------------|
| PHP Version | 8.0+ | 8.2 or higher |
| WordPress | 6.0+ | 6.4 or higher |
| MySQL | 5.6+ | 8.0+ |
| Memory (RAM) | 128 MB | 512 MB+ |
| Disk Space | 50 MB free | 1 GB+ free |

### PHP Extensions

Ensure these PHP extensions are enabled:

- `openssl` - For API token encryption
- `curl` - For remote requests
- `json` - For data processing
- `mbstring` - For string handling
- `zip` - For updates
- `zlib` - For GZIP compression

### WordPress Configuration

- `WP_CACHE` constant must be enabled (Blitz Cache enables this automatically)
- Write permissions for `wp-content/cache/` directory
- No conflicting cache plugins installed

---

## Installation Methods

### Method 1: WordPress Admin (Recommended)

This is the easiest method for most users.

#### Step 1: Access Plugins Menu

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**

#### Step 2: Search for Blitz Cache

1. In the search box, type: `Blitz Cache`
2. Look for the official plugin by **Ersin KOÇ**

#### Step 3: Install and Activate

1. Click **Install Now** on the Blitz Cache plugin
2. Wait for installation to complete
3. Click **Activate**

#### Step 4: Automatic Configuration

Blitz Cache will automatically:
- Enable the `WP_CACHE` constant
- Create cache directory
- Install the advanced cache dropin
- Set default settings
- Schedule cache warmup

**That's it!** Blitz Cache is now active and caching your site.

---

### Method 2: WP-CLI

For advanced users and developers.

#### Prerequisites

- WP-CLI installed on your server
- SSH access to your server

#### Installation Commands

```bash
# Download and install Blitz Cache
wp plugin install https://github.com/ersinkoc/blitz-cache/archive/main.zip --activate

# Or install from WordPress.org
wp plugin install blitz-cache --activate

# Verify installation
wp plugin list
```

#### Verification

```bash
# Check if Blitz Cache is active
wp plugin status blitz-cache

# View cache status
wp option get blitz_cache_settings

# Clear cache
wp cache flush
```

---

### Method 3: Manual Upload

If you need to upload files directly.

#### Step 1: Download Plugin

1. Download the latest version from [GitHub Releases](https://github.com/ersinkoc/blitz-cache/releases)
2. Extract the zip file

#### Step 2: Upload to Server

Upload the plugin folder to your server:

```bash
# Via SCP
scp -r blitz-cache-main.zip user@server:/path/to/wordpress/wp-content/plugins/

# Via SFTP
# Use your SFTP client to upload to: /wp-content/plugins/

# Via rsync
rsync -avz /local/path/blitz-cache/ user@server:/path/to/wordpress/wp-content/plugins/
```

#### Step 3: Extract and Activate

```bash
# SSH into your server
ssh user@server

# Navigate to plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Extract the plugin
unzip blitz-cache-main.zip

# Rename if needed
mv blitz-cache-main blitz-cache

# Set proper permissions
chown -R www-data:www-data blitz-cache/
chmod -R 755 blitz-cache/
```

#### Step 4: Activate via WordPress Admin

1. Go to **Plugins** in WordPress admin
2. Find **Blitz Cache**
3. Click **Activate**

---

### Method 4: GitHub

For developers who want the latest version.

#### Using Git

```bash
# Clone the repository
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/ersinkoc/blitz-cache.git

# Or use GitHub CLI
gh repo clone ersinkoc/blitz-cache

# Set proper permissions
chown -R www-data:www-data blitz-cache/
chmod -R 755 blitz-cache/
```

#### Using Composer

```json
{
    "require": {
        "ersinkoc/blitz-cache": "^1.0"
    }
}
```

Then run:

```bash
composer install
```

---

## Post-Installation

After installation, Blitz Cache automatically configures itself. However, we recommend:

### 1. Verify Settings

Go to **Blitz Cache** in your WordPress admin menu to:

- Verify cache is enabled (default: enabled)
- Check cache TTL (default: 24 hours)
- Review compression settings (GZIP and minification are enabled by default)

### 2. Configure Exclusions (Optional)

Add exclusions for pages that shouldn't be cached:

- User account pages
- Custom login pages
- API endpoints
- Admin pages

**Path:** `Blitz Cache > Settings > Exclusions`

### 3. Set Up Cloudflare (Optional)

If you use Cloudflare:

1. Go to **Blitz Cache > Cloudflare**
2. Create a Cloudflare API token
3. Enter the token and test connection
4. Select your zone
5. Save settings

**See:** [Cloudflare Setup Guide](cloudflare.md)

### 4. Enable Cache Warmup (Optional)

Blitz Cache includes cache warmup to automatically refill cache after purging:

1. Go to **Blitz Cache > Settings > Preload**
2. Enable "Cache Warmup" (default: enabled)
3. Choose warmup source:
   - **Sitemap** - Use your XML sitemap
   - **Menu** - Use navigation menus
   - **Custom** - Provide custom URLs via filter
4. Set warmup interval (default: 6 hours)
5. Save settings

---

## Verification

### Check Cache Status

After installation, verify Blitz Cache is working:

#### Method 1: Admin Dashboard

1. Go to **Blitz Cache** in WordPress admin
2. Check the **Dashboard** tab
3. Verify:
   - Status shows **Active**
   - Cache directory is writable
   - WP_CACHE constant is enabled

#### Method 2: Browser Developer Tools

1. Open your site in a browser
2. Press **F12** to open developer tools
3. Go to **Network** tab
4. Reload the page
5. Check response headers for:
   ```
   X-Blitz-Cache: HIT
   ```
   or
   ```
   X-Blitz-Cache: MISS
   ```

#### Method 3: Command Line

```bash
# Check cache files
ls -la wp-content/cache/blitz-cache/pages/

# Verify advanced-cache.php exists
ls -la wp-content/advanced-cache.php

# Check if WP_CACHE is enabled
grep -r "WP_CACHE" wp-config.php
```

#### Method 4: PHP

Add this to your theme's `functions.php`:

```php
// Debug function to check cache status
function debug_cache_status() {
    if (current_user_can('manage_options')) {
        $options = get_option('blitz_cache_settings', []);
        echo '<pre>';
        print_r([
            'Cache Enabled' => $options['page_cache_enabled'] ?? false,
            'Cache TTL' => $options['page_cache_ttl'] ?? 0,
            'GZIP Enabled' => $options['gzip_enabled'] ?? false,
            'Cache Directory' => BLITZ_CACHE_CACHE_DIR,
            'Cache Directory Exists' => is_dir(BLITZ_CACHE_CACHE_DIR),
            'Cache Directory Writable' => is_writable(BLITZ_CACHE_CACHE_DIR),
            'WP_CACHE' => defined('WP_CACHE') && WP_CACHE ? 'Enabled' : 'Disabled',
        ]);
        echo '</pre>';
    }
}
```

Then call `debug_cache_status()` in your template.

---

## Troubleshooting

### Issue: Cache Not Working

**Symptoms:**
- No "X-Blitz-Cache" header
- Page load time hasn't improved

**Solutions:**

1. **Check WP_CACHE constant:**
```bash
grep WP_CACHE wp-config.php
```
If not found, add:
```php
define('WP_CACHE', true);
```

2. **Check file permissions:**
```bash
chown -R www-data:www-data wp-content/cache/blitz-cache/
chmod -R 755 wp-content/cache/blitz-cache/
```

3. **Check for conflicts:**
   - Deactivate other cache plugins
   - Check `.htaccess` for conflicting rules

### Issue: Cache Directory Not Writable

**Symptoms:**
- Warning in admin dashboard
- Cache files not created

**Solutions:**

1. **Fix permissions:**
```bash
chmod 755 wp-content/cache/blitz-cache/
```

2. **Check SELinux** (if applicable):
```bash
# Allow PHP to write to cache directory
setsebool -P httpd_unified 1
```

### Issue: Advanced Cache Dropin Not Installing

**Symptoms:**
- Cache doesn't serve from dropin
- Pages still generate dynamically

**Solutions:**

1. **Manual installation:**
```bash
cp wp-content/plugins/blitz-cache/advanced-cache.php wp-content/advanced-cache.php
```

2. **Check permissions:**
```bash
chmod 644 wp-content/advanced-cache.php
```

### Issue: Cloudflare Integration Not Working

**Symptoms:**
- Cloudflare connection fails
- URLs not purging from Cloudflare

**Solutions:**

1. **Verify API token has correct permissions:**
   - Zone:Zone:Read
   - Zone:Cache:Edit

2. **Check zone ID is correct:**
   - Get from Cloudflare dashboard

3. **Test token manually:**
```bash
curl -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Issue: Cache Warmup Not Running

**Symptoms:**
- Cache stays empty after purging
- Warmup doesn't populate cache

**Solutions:**

1. **Check cron is running:**
```bash
wp cron event list
```

2. **Trigger manually:**
```bash
wp eval "do_action('blitz_cache_warmup_cron');"
```

3. **Check warmup source:**
   - Verify sitemap exists: `/wp-sitemap.xml`
   - Check menu is set up properly

### Issue: High Memory Usage

**Symptoms:**
- Site crashes or shows errors
- Out of memory errors

**Solutions:**

1. **Reduce batch size:**
   - Go to `Blitz Cache > Settings > Preload`
   - Set "Warmup Batch Size" to 1-3

2. **Increase PHP memory limit:**
```php
ini_set('memory_limit', '512M');
```
or in `wp-config.php`:
```php
ini_set('memory_limit', '512M');
```

3. **Disable heavy features:**
   - Turn off HTML minification
   - Turn off GZIP compression

---

## Uninstallation

### Method 1: WordPress Admin

1. Go to **Plugins**
2. Find **Blitz Cache**
3. Click **Deactivate**
4. Click **Delete**
5. Choose whether to keep settings:
   - **Keep Settings** - Settings remain in database (recommended)
   - **Delete Settings** - Completely remove all data

### Method 2: WP-CLI

```bash
# Deactivate plugin
wp plugin deactivate blitz-cache

# Delete plugin
wp plugin delete blitz-cache

# Clear cache (optional)
wp cache flush
```

### Method 3: Manual

1. **Deactivate plugin** in WordPress admin
2. **Delete plugin files:**
```bash
rm -rf wp-content/plugins/blitz-cache/
rm wp-content/advanced-cache.php
```
3. **Clean up database** (optional):
```sql
DELETE FROM wp_options WHERE option_name LIKE 'blitz_cache%';
```

---

## Performance Tips

### After Installation

1. **Monitor cache hit ratio:**
   - Go to `Blitz Cache > Dashboard`
   - Aim for 90%+ hit ratio

2. **Adjust TTL based on content:**
   - Homepage: 2-6 hours
   - Blog posts: 12-24 hours
   - Static pages: 24-48 hours

3. **Optimize exclusions:**
   - Exclude pages that change frequently
   - Exclude user-specific content

### For High-Traffic Sites

1. **Enable GZIP compression** (default: enabled)
2. **Enable HTML minification** (default: enabled)
3. **Use separate mobile cache** if site differs on mobile
4. **Set up Cloudflare Workers** for edge caching

### For E-commerce Sites

1. **Enable WooCommerce integration** (automatic)
2. **Exclude cart/checkout pages** (automatic)
3. **Purge product pages on updates** (automatic)
4. **Consider logged-in user caching** for public product pages

---

## Common Questions

### Q: Can I use Blitz Cache with other cache plugins?

**A:** No, it's not recommended. Blitz Cache will conflict with:
- WP Rocket
- W3 Total Cache
- WP Super Cache
- LiteSpeed Cache

Use only one cache plugin at a time.

### Q: Does Blitz Cache work with page builders?

**A:** Yes! Blitz Cache works with:
- Elementor
- Beaver Builder
- Divi
- Gutenberg
- Visual Composer

### Q: Can I cache logged-in users?

**A:** Yes, but it's disabled by default. Enable it in:
`Blitz Cache > Settings > Page Cache > Cache Logged-in Users`

**Note:** This may serve personalized content to other users.

### Q: How do I exclude specific pages?

**A:** Go to `Blitz Cache > Settings > Exclusions` and add URL patterns.

**Example patterns:**
- `/cart/*` - Exclude all cart pages
- `*checkout*` - Exclude pages with "checkout" in URL
- `?add-to-cart=*` - Exclude add-to-cart URLs

### Q: What happens when I update content?

**A:** Blitz Cache automatically:
1. Purges the updated page
2. Purges related pages (categories, tags, archives)
3. Purges from Cloudflare (if configured)
4. Refills cache (if warmup is enabled)

### Q: Can I export/import settings?

**A:** Yes! Go to `Blitz Cache > Tools > Settings Management`

---

## Getting Help

### Documentation

- [Configuration Guide](configuration.md)
- [Cloudflare Setup](cloudflare.md)
- [Hooks Reference](HOOKS.md)
- [Troubleshooting](troubleshooting.md)

### Support Channels

1. **GitHub Issues** - For bugs and feature requests:
   https://github.com/ersinkoc/blitz-cache/issues

2. **WordPress.org Forum** - For general support:
   https://wordpress.org/support/plugin/blitz-cache

3. **Email** - For urgent issues:
   support@blitzcache.dev

### Before Asking for Help

Please gather this information:

1. WordPress version
2. PHP version
3. Blitz Cache version
4. Active theme and plugins
5. Error messages (if any)
6. Steps to reproduce the issue

---

## Next Steps

Now that Blitz Cache is installed:

1. ✅ Read the [Configuration Guide](configuration.md)
2. ✅ Set up [Cloudflare Integration](cloudflare.md)
3. ✅ Review [Performance Tuning](performance.md)
4. ✅ Check [Developer Hooks](HOOKS.md)

---

**Congratulations!** Blitz Cache is now installed and ready to speed up your site! 🚀
