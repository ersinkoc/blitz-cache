<?php
/**
 * Centralized Logging System for Blitz Cache
 *
 * Provides thread-safe logging with configurable levels,
 * automatic log rotation, and WordPress integration.
 *
 * @package Blitz_Cache
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Logger Class
 *
 * Handles all logging operations for the Blitz Cache plugin.
 */
class Blitz_Cache_Logger {

	/**
	 * Log levels and their priorities.
	 *
	 * @var array
	 */
	private array $log_levels = [
		'DEBUG'    => 1,
		'INFO'     => 2,
		'WARNING'  => 3,
		'ERROR'    => 4,
		'CRITICAL' => 5,
	];

	/**
	 * Minimum log level to record.
	 *
	 * @var int
	 */
	private int $min_level;

	/**
	 * Log directory path.
	 *
	 * @var string
	 */
	private string $log_dir;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self Logger instance.
	 */
	public static function get_instance(): self {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->log_dir  = BLITZ_CACHE_CACHE_DIR . 'logs/';
		$this->min_level = $this->get_log_level();
		$this->ensure_log_directory();
	}

	/**
	 * Get the configured log level from options.
	 *
	 * @return int Log level priority.
	 */
	private function get_log_level(): int {
		// Check if WP_DEBUG is enabled
		if (!defined('WP_DEBUG') || !WP_DEBUG) {
			// Only log errors and critical when debug is off
			return 4;
		}

		// Get log level from option if available
		$level = get_option('blitz_cache_log_level', 'INFO');
		return $this->log_levels[ $level ] ?? 2;
	}

	/**
	 * Ensure log directory exists and is writable.
	 */
	private function ensure_log_directory(): void {
		if (!file_exists($this->log_dir)) {
			wp_mkdir_p($this->log_dir);

			// Protect log directory with .htaccess
			$htaccess = $this->log_dir . '.htaccess';
			if (!file_exists($htaccess)) {
				file_put_contents($htaccess, "Deny from all\n");
			}

			// Add index.php for directory protection
			$index = $this->log_dir . 'index.php';
			if (!file_exists($index)) {
				file_put_contents($index, "<?php\n// Silence is golden.\n");
			}
		}
	}

	/**
	 * Log a message at a specific level.
	 *
	 * @param string $level   Log level (DEBUG, INFO, WARNING, ERROR, CRITICAL).
	 * @param string $message Log message.
	 * @param array  $context Contextual information.
	 */
	public function log(string $level, string $message, array $context = []): void {
		// Validate log level
		if (!isset($this->log_levels[ $level ])) {
			$level = 'INFO';
		}

		// Check if this level should be logged
		if ($this->log_levels[ $level ] < $this->min_level) {
			return;
		}

		$entry = [
			'timestamp'  => gmdate('Y-m-d H:i:s'),
			'level'      => $level,
			'message'    => $message,
			'context'    => $context,
			'request_uri' => $this->get_request_uri(),
			'user_id'    => $this->get_user_id(),
			'memory'     => memory_get_usage(true),
		];

		$this->write_log($entry);
	}

	/**
	 * Get the current request URI safely.
	 *
	 * @return string Request URI or 'CLI'.
	 */
	private function get_request_uri(): string {
		if (php_sapi_name() === 'cli') {
			return 'CLI';
		}
		return isset($_SERVER['REQUEST_URI']) ? esc_url_raw($_SERVER['REQUEST_URI']) : 'unknown';
	}

	/**
	 * Get the current user ID.
	 *
	 * @return int User ID.
	 */
	private function get_user_id(): int {
		if (!function_exists('get_current_user_id')) {
			return 0;
		}
		return get_current_user_id();
	}

	/**
	 * Write log entry to file.
	 *
	 * @param array $entry Log entry.
	 */
	private function write_log(array $entry): void {
		$log_file = $this->log_dir . 'blitz-cache-' . gmdate('Y-m-d') . '.log';
		$line     = wp_json_encode($entry) . PHP_EOL;

		// Atomic write with file locking
		$handle = @fopen($log_file, 'a');
		if ($handle && flock($handle, LOCK_EX)) {
			fwrite($handle, $line);
			flock($handle, LOCK_UN);
			fclose($handle);
		}

		// Rotate logs if needed
		$this->rotate_logs();
	}

	/**
	 * Rotate old log files.
	 *
	 * Keeps logs for 30 days and removes files larger than 10MB.
	 */
	private function rotate_logs(): void {
		// Only run rotation once per day
		$last_rotation = get_option('blitz_cache_log_rotation', 0);
		if (time() - $last_rotation < DAY_IN_SECONDS) {
			return;
		}

		$files = glob($this->log_dir . 'blitz-cache-*.log');
		if (!$files) {
			return;
		}

		$now = time();

		foreach ($files as $file) {
			// Remove files older than 30 days
			$file_age = $now - filemtime($file);
			if ($file_age > 30 * DAY_IN_SECONDS) {
				@unlink($file);
				continue;
			}

			// Rotate files larger than 10MB
			if (filesize($file) > 10 * 1024 * 1024) {
				$this->rotate_large_file($file);
			}
		}

		update_option('blitz_cache_log_rotation', $now);
	}

	/**
	 * Rotate a single large log file.
	 *
	 * @param string $file File path.
	 */
	private function rotate_large_file(string $file): void {
		$base_name = basename($file, '.log');
		$counter   = 1;

		// Find next available rotation number
		while (file_exists($this->log_dir . $base_name . '.' . $counter . '.log')) {
			++$counter;
		}

		$rotated_file = $this->log_dir . $base_name . '.' . $counter . '.log';
		rename($file, $rotated_file);
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Contextual information.
	 */
	public function debug(string $message, array $context = []): void {
		$this->log('DEBUG', $message, $context);
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Contextual information.
	 */
	public function info(string $message, array $context = []): void {
		$this->log('INFO', $message, $context);
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Contextual information.
	 */
	public function warning(string $message, array $context = []): void {
		$this->log('WARNING', $message, $context);
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Contextual information.
	 */
	public function error(string $message, array $context = []): void {
		$this->log('ERROR', $message, $context);

		// Also log to WordPress error log for errors
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('[Blitz Cache ERROR] ' . $message);
		}
	}

	/**
	 * Log a critical message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Contextual information.
	 */
	public function critical(string $message, array $context = []): void {
		$this->log('CRITICAL', $message, $context);

		// Always log critical to WordPress error log
		error_log('[Blitz Cache CRITICAL] ' . $message);

		// Trigger admin notice for critical errors
		add_action('admin_notices', function() use ($message) {
			$class   = 'notice notice-error';
			$message = sprintf(
				/* translators: %s: Error message */
				__('Blitz Cache Critical Error: %s', 'blitz-cache'),
				esc_html($message)
			);
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
		});
	}

	/**
	 * Get recent log entries.
	 *
	 * @param int  $count Number of entries to retrieve.
	 * @param bool $errors_only Only retrieve error/critical entries.
	 * @return array Recent log entries.
	 */
	public function get_recent_entries(int $count = 50, bool $errors_only = false): array {
		$log_file = $this->log_dir . 'blitz-cache-' . gmdate('Y-m-d') . '.log';

		if (!file_exists($log_file)) {
			return [];
		}

		$entries = [];
		$handle  = @fopen($log_file, 'r');

		if ($handle) {
			// Seek to end and go backwards
			fseek($handle, 0, SEEK_END);
			$pos   = ftell($handle);
			$lines = [];

			while ($pos > 0 && count($lines) < $count * 2) {
				fseek($handle, --$pos);
				$char = fgetc($handle);

				if ($char === "\n") {
					$line = trim(fgets($handle));
					if ($line) {
						array_unshift($lines, $line);
					}
				}
			}

			fclose($handle);

			// Parse and filter entries
			foreach ($lines as $line) {
				$entry = json_decode($line, true);

				if (!$entry) {
					continue;
				}

				if ($errors_only && !in_array($entry['level'], ['ERROR', 'CRITICAL'], true)) {
					continue;
				}

				$entries[] = $entry;

				if (count($entries) >= $count) {
					break;
				}
			}
		}

		return $entries;
	}

	/**
	 * Clear all log files.
	 *
	 * @return int Number of files deleted.
	 */
	public function clear_logs(): int {
		$files = glob($this->log_dir . 'blitz-cache-*.log');
		$count = 0;

		if ($files) {
			foreach ($files as $file) {
				if (@unlink($file)) {
					++$count;
				}
			}
		}

		return $count;
	}

	/**
	 * Get total log size in bytes.
	 *
	 * @return int Total size in bytes.
	 */
	public function get_log_size(): int {
		$files = glob($this->log_dir . 'blitz-cache-*.log');
		$size  = 0;

		if ($files) {
			foreach ($files as $file) {
				$size += filesize($file);
			}
		}

		return $size;
	}
}
