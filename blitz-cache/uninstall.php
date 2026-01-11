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

    // Try to remove WP_CACHE constant with safer approach
    $config_file = ABSPATH . 'wp-config.php';
    if (is_writable($config_file)) {
        // Create backup before modification
        $backup_file = $config_file . '.blitz_backup.' . time();
        if (!copy($config_file, $backup_file)) {
            // Skip modification if backup fails
            error_log('Blitz Cache: Failed to backup wp-config.php, skipping modification');
        } else {
            $config = file_get_contents($config_file);
            $original_config = $config;
            $config = preg_replace("/define\s*\(\s*['\"]WP_CACHE['\"]\s*,\s*true\s*\)\s*;\s*\/\/\s*Added by Blitz Cache\n?/", '', $config);

            // Only write if content actually changed
            if ($config !== $original_config) {
                // Write to temp file first for atomic operation
                $temp_file = $config_file . '.tmp.' . uniqid();
                $result = @file_put_contents($temp_file, $config, LOCK_EX);

                if ($result !== false && @rename($temp_file, $config_file)) {
                    // Success, clean up backup after a delay
                    wp_schedule_single_event(time() + HOUR_IN_SECONDS, 'blitz_cache_cleanup_config_backup', [$backup_file]);
                } else {
                    // Restore from backup on failure
                    @unlink($temp_file);
                    error_log('Blitz Cache: Failed to write wp-config.php, restored from backup');
                }
            } else {
                // No changes needed, remove backup
                @unlink($backup_file);
            }
        }
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
