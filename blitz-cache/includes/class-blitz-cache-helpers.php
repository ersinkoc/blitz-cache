<?php
/**
 * Utility Helper Class for Blitz Cache
 *
 * Provides common utility functions for file operations, formatting, etc.
 *
 * @package Blitz_Cache
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Helpers Class
 *
 * Static utility methods for common operations.
 */
class Blitz_Cache_Helpers {

	/**
	 * Safely write to a file with atomic operation.
	 *
	 * Uses temp file and rename for atomic write.
	 *
	 * @param string $file   File path to write.
	 * @param string $content Content to write.
	 * @return bool True on success, false on failure.
	 */
	public static function atomic_file_write(string $file, string $content): bool {
		$dir = dirname($file);

		// Ensure directory exists
		if (!file_exists($dir) && !wp_mkdir_p($dir)) {
			return false;
		}

		// Write to temp file first
		$temp_file = $file . '.tmp.' . uniqid();
		$result    = @file_put_contents($temp_file, $content, LOCK_EX);

		if ($result === false) {
			@unlink($temp_file);
			return false;
		}

		// Atomic rename
		if (!@rename($temp_file, $file)) {
			@unlink($temp_file);
			return false;
		}

		// Set secure permissions
		@chmod($file, 0644);
		return true;
	}

	/**
	 * Safely read a JSON file with error handling.
	 *
	 * @param string $file   File path to read.
	 * @param mixed  $default Default value if file doesn't exist or is invalid.
	 * @return mixed Decoded data or default value.
	 */
	public static function read_json_file(string $file, $default = []) {
		if (!file_exists($file)) {
			return $default;
		}

		$content = @file_get_contents($file);
		if ($content === false) {
			return $default;
		}

		$data = @json_decode($content, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return $default;
		}

		return $data;
	}

	/**
	 * Format bytes to human-readable size.
	 *
	 * @param int    $bytes    Number of bytes.
	 * @param int    $precision Decimal precision.
	 * @return string Formatted size (e.g., "1.5 MB").
	 */
	public static function format_bytes(int $bytes, int $precision = 2): string {
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];

		for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
			$bytes /= 1024;
		}

		return round($bytes, $precision) . ' ' . $units[$i];
	}

	/**
	 * Format seconds to human-readable time.
	 *
	 * @param int $seconds Number of seconds.
	 * @return string Formatted time (e.g., "2h 30m").
	 */
	public static function format_time(int $seconds): string {
		if ($seconds < 60) {
			return $seconds . 's';
		}

		if ($seconds < 3600) {
			$minutes = floor($seconds / 60);
			return $minutes . 'm';
		}

		if ($seconds < 86400) {
			$hours   = floor($seconds / 3600);
			$minutes = floor(($seconds % 3600) / 60);
			return $minutes > 0 ? $hours . 'h ' . $minutes . 'm' : $hours . 'h';
		}

		$days  = floor($seconds / 86400);
		$hours = floor(($seconds % 86400) / 3600);
		return $hours > 0 ? $days . 'd ' . $hours . 'h' : $days . 'd';
	}

	/**
	 * Check if we're in a CLI context.
	 *
	 * @return bool True if running in CLI.
	 */
	public static function is_cli(): bool {
		return php_sapi_name() === 'cli';
	}

	/**
	 * Generate a random token.
	 *
	 * @param int $length Token length.
	 * @return string Random token.
	 */
	public static function generate_token(int $length = 32): string {
		$bytes = random_bytes($length);
		return bin2hex($bytes);
	}

	/**
	 * Truncate a string to a maximum length.
	 *
	 * @param string $string String to truncate.
	 * @param int    $length Maximum length.
	 * @param string $suffix Suffix to add if truncated.
	 * @return string Truncated string.
	 */
	public static function truncate(string $string, int $length, string $suffix = '...'): string {
		if (strlen($string) <= $length) {
			return $string;
		}

		return substr($string, 0, $length - strlen($suffix)) . $suffix;
	}

	/**
	 * Get a value from nested array using dot notation.
	 *
	 * @param array  $array   Array to search.
	 * @param string $key     Key in dot notation (e.g., "user.name").
	 * @param mixed  $default Default value if key not found.
	 * @return mixed Found value or default.
	 */
	public static function array_get(array $array, string $key, $default = null) {
		$segments = explode('.', $key);

		foreach ($segments as $segment) {
			if (!isset($array[$segment])) {
				return $default;
			}
			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Set a value in nested array using dot notation.
	 *
	 * @param array  $array Array to modify (passed by reference).
	 * @param string $key   Key in dot notation.
	 * @param mixed  $value Value to set.
	 */
	public static function array_set(array &$array, string $key, $value): void {
		$segments = explode('.', $key);
		$current  = &$array;

		foreach ($segments as $i => $segment) {
			if (count($segments) === 1) {
				break;
			}

			if (!isset($current[$segment]) || !is_array($current[$segment])) {
				$current[$segment] = [];
			}

			$current = &$current[$segment];
		}

		$current[array_pop($segments)] = $value;
	}

	/**
	 * Check if a string starts with a substring.
	 *
	 * @param string $haystack String to search in.
	 * @param string $needle   Substring to search for.
	 * @return bool True if string starts with needle.
	 */
	public static function starts_with(string $haystack, string $needle): bool {
		return strncmp($haystack, $needle, strlen($needle)) === 0;
	}

	/**
	 * Check if a string ends with a substring.
	 *
	 * @param string $haystack String to search in.
	 * @param string $needle   Substring to search for.
	 * @return bool True if string ends with needle.
	 */
	public static function ends_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		if ($length === 0) {
			return true;
		}
		return substr($haystack, -$length) === $needle;
	}

	/**
	 * Get current URL.
	 *
	 * @return string Current URL.
	 */
	public static function get_current_url(): string {
		$protocol = is_ssl() ? 'https://' : 'http://';
		return $protocol . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
	}

	/**
	 * Clean and normalize a URL.
	 *
	 * @param string $url URL to clean.
	 * @return string Cleaned URL.
	 */
	public static function clean_url(string $url): string {
		$url = trim($url);
		$url = sanitize_url($url);
		$url = untrailingslashit($url);
		return $url;
	}

	/**
	 * Get cache size for a directory.
	 *
	 * @param string $dir Directory to scan.
	 * @return int Total size in bytes.
	 */
	public static function get_dir_size(string $dir): int {
		$size = 0;

		if (!file_exists($dir)) {
			return $size;
		}

		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
			if ($file->isFile()) {
				$size += $file->getSize();
			}
		}

		return $size;
	}

	/**
	 * Count files in a directory matching a pattern.
	 *
	 * @param string $dir    Directory to scan.
	 * @param string $pattern File pattern (e.g., "*.html").
	 * @return int Number of matching files.
	 */
	public static function count_files(string $dir, string $pattern = '*'): int {
		if (!file_exists($dir)) {
			return 0;
		}

		$files = glob($dir . $pattern);
		return $files ? count($files) : 0;
	}

	/**
	 * Convert a value to boolean.
	 *
	 * Handles various string representations of boolean values.
	 *
	 * @param mixed $value Value to convert.
	 * @return bool Boolean value.
	 */
	public static function to_bool($value): bool {
		if (is_bool($value)) {
			return $value;
		}

		if (is_numeric($value)) {
			return (bool) $value;
		}

		if (is_string($value)) {
			$value = strtolower($value);
			return in_array($value, ['true', 'yes', 'on', '1'], true);
		}

		return false;
	}

	/**
	 * Get a list of all cache files.
	 *
	 * @return array List of cache file paths.
	 */
	public static function get_cache_files(): array {
		$cache_dir = BLITZ_CACHE_CACHE_DIR . 'pages/';

		if (!file_exists($cache_dir)) {
			return [];
		}

		$files = glob($cache_dir . '*.html*');
		return $files ?: [];
	}

	/**
	 * Clear all files in a directory.
	 *
	 * @param string $dir Directory to clear.
	 * @return int Number of files deleted.
	 */
	public static function clear_dir(string $dir): int {
		if (!file_exists($dir)) {
			return 0;
		}

		$count   = 0;
		$files   = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $file) {
			if ($file->isFile()) {
				if (@unlink($file->getPathname())) {
					$count++;
				}
			} elseif ($file->isDir()) {
				@rmdir($file->getPathname());
			}
		}

		return $count;
	}

	/**
	 * Sanitize a filename to remove dangerous characters.
	 *
	 * @param string $filename Original filename.
	 * @return string Sanitized filename.
	 */
	public static function sanitize_filename(string $filename): string {
		// Remove dangerous characters
		$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

		// Remove multiple dots
		$filename = preg_replace('/\.{2,}/', '.', $filename);

		// Trim dots from start/end
		$filename = trim($filename, '.');

		return $filename;
	}
}
