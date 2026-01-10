<?php
/**
 * Validator Helper Class for Blitz Cache
 *
 * Provides validation functions for URLs, paths, cache keys, and more.
 *
 * @package Blitz_Cache
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Validator Class
 *
 * Static utility methods for validating various inputs.
 */
class Blitz_Cache_Validator {

	/**
	 * Validate a file path is within the allowed directory.
	 *
	 * Prevents directory traversal attacks.
	 *
	 * @param string $path        Path to validate.
	 * @param string $allowed_dir Allowed base directory.
	 * @return bool True if path is valid and within allowed directory.
	 */
	public static function validate_path(string $path, string $allowed_dir): bool {
		$real_path    = realpath($path);
		$real_allowed = realpath($allowed_dir);

		// If path doesn't exist, check parent directory
		if ($real_path === false) {
			$parent    = dirname($path);
			$real_parent = realpath($parent);

			if ($real_parent === false) {
				return false;
			}

			$real_path = $real_parent . '/' . basename($path);
		}

		if ($real_allowed === false) {
			return false;
		}

		// Normalize paths for comparison
		$real_path    = str_replace('\\', '/', $real_path);
		$real_allowed = str_replace('\\', '/', $real_allowed);

		// Ensure path starts with allowed directory
		return strpos($real_path, $real_allowed) === 0;
	}

	/**
	 * Validate URL is safe and well-formed.
	 *
	 * @param string $url URL to validate.
	 * @return bool True if URL is valid.
	 */
	public static function validate_url(string $url): bool {
		$parsed = parse_url($url);

		if ($parsed === false) {
			return false;
		}

		// Check scheme
		if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'], true)) {
			return false;
		}

		// Check host
		if (!isset($parsed['host']) || empty($parsed['host'])) {
			return false;
		}

		return true;
	}

	/**
	 * Validate cache key format.
	 *
	 * Cache keys should be 32-character MD5 hashes.
	 *
	 * @param string $key Cache key to validate.
	 * @return bool True if key is valid MD5 hash format.
	 */
	public static function validate_cache_key(string $key): bool {
		// MD5 hashes are 32 hexadecimal characters
		return (bool) preg_match('/^[a-f0-9]{32}$/i', $key);
	}

	/**
	 * Sanitize and validate tab parameter with whitelist.
	 *
	 * @param string $tab     Raw tab value.
	 * @param array  $allowed Optional list of allowed tabs.
	 * @return string Validated tab key.
	 */
	public static function validate_tab(string $tab, array $allowed = []): string {
		$allowed = $allowed ?: ['dashboard', 'settings', 'cloudflare', 'tools'];
		$tab = sanitize_key($tab);

		return in_array($tab, $allowed, true) ? $tab : 'dashboard';
	}

	/**
	 * Validate email address.
	 *
	 * @param string $email Email to validate.
	 * @return bool True if email is valid.
	 */
	public static function validate_email(string $email): bool {
		return is_email($email) !== false;
	}

	/**
	 * Validate URL pattern for exclusions.
	 *
	 * @param string $pattern Pattern to validate.
	 * @return bool True if pattern is safe.
	 */
	public static function validate_url_pattern(string $pattern): bool {
		// Basic check for dangerous patterns
		$dangerous = ['../', './', '\\', '\0', "\x00"];
		foreach ($dangerous as $char) {
			if (strpos($pattern, $char) !== false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate integer is within range.
	 *
	 * @param mixed $value    Value to validate.
	 * @param int   $min      Minimum value.
	 * @param int   $max      Maximum value.
	 * @param bool  $allow_empty Whether empty values are allowed.
	 * @return bool True if value is valid.
	 */
	public static function validate_int_range($value, int $min = 0, int $max = PHP_INT_MAX, bool $allow_empty = false): bool {
		if ($allow_empty && ($value === '' || $value === null)) {
			return true;
		}

		if (!is_numeric($value)) {
			return false;
		}

		$int_value = (int) $value;
		return $int_value >= $min && $int_value <= $max;
	}

	/**
	 * Validate boolean value.
	 *
	 * @param mixed $value Value to validate.
	 * @return bool True if value is boolean-like.
	 */
	public static function validate_bool($value): bool {
		return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false', 'yes', 'no', 'on', 'off'], true);
	}

	/**
	 * Sanitize a comma-separated list of values.
	 *
	 * @param string $list Comma-separated list.
	 * @return array Array of sanitized values.
	 */
	public static function sanitize_list(string $list): array {
		if (empty($list)) {
			return [];
		}

		$items = explode(',', $list);
		$items = array_map('trim', $items);
		$items = array_map('sanitize_text_field', $items);
		$items = array_filter($items);

		return array_values($items);
	}
}
