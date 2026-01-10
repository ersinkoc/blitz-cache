<?php
/**
 * Centralized Error Handler for Blitz Cache
 *
 * Provides consistent error handling with automatic logging
 * and graceful degradation throughout the plugin.
 *
 * @package Blitz_Cache
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Error Handler Class
 *
 * Centralizes error handling logic with automatic logging
 * and recovery strategies.
 */
class Blitz_Cache_Error_Handler {

	/**
	 * Logger instance.
	 *
	 * @var Blitz_Cache_Logger
	 */
	private Blitz_Cache_Logger $logger;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self Error handler instance.
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
		$this->logger = Blitz_Cache_Logger::get_instance();
	}

	/**
	 * Handle a file operation with error handling.
	 *
	 * Wraps file operations with try-catch and automatic logging.
	 * Returns false on failure, operation result on success.
	 *
	 * @param string   $operation Description of the operation.
	 * @param callable $callback  The operation to execute.
	 * @param array    $context   Contextual information.
	 * @return mixed Operation result or false on failure.
	 */
	public function handle_file_operation(string $operation, callable $callback, array $context = []) {
		try {
			$result = $callback();

			// Check for false return (common in WP file functions)
			if ($result === false) {
				$this->logger->error(
					"File operation failed: {$operation}",
					array_merge($context, [
						'operation' => $operation,
						'error'     => 'Callback returned false',
					])
				);
				return false;
			}

			$this->logger->debug(
				"File operation succeeded: {$operation}",
				array_merge($context, ['operation' => $operation])
			);

			return $result;

		} catch (Blitz_Cache_File_Exception $e) {
			$this->logger->error(
				"File operation failed: {$operation}",
				array_merge($context, [
					'operation' => $operation,
					'error'     => $e->getMessage(),
					'trace'     => $e->getTraceAsString(),
					'exception_context' => $e->get_context(),
				])
			);
			return false;

		} catch (Blitz_Cache_Permission_Exception $e) {
			$this->logger->error(
				"Permission denied: {$operation}",
				array_merge($context, [
					'operation' => $operation,
					'error'     => $e->getMessage(),
					'exception_context' => $e->get_context(),
				])
			);
			return false;

		} catch (Exception $e) {
			$this->logger->error(
				"Unexpected error in: {$operation}",
				array_merge($context, [
					'operation' => $operation,
					'error'     => $e->getMessage(),
					'type'      => get_class($e),
					'trace'     => $e->getTraceAsString(),
				])
			);
			return false;
		}
	}

	/**
	 * Handle a validation operation.
	 *
	 * Throws on validation failure, logs the event.
	 *
	 * @param callable $callback The validation callback.
	 * @param array    $context  Contextual information.
	 * @return mixed Validation result.
	 * @throws Blitz_Cache_Validation_Exception On validation failure.
	 */
	public function handle_validation(callable $callback, array $context = []) {
		try {
			return $callback();

		} catch (Blitz_Cache_Validation_Exception $e) {
			$this->logger->warning(
				'Validation failed',
				array_merge($context, [
					'error'     => $e->getMessage(),
					'exception_context' => $e->get_context(),
				])
			);
			throw $e;
		}
	}

	/**
	 * Handle a network/API operation.
	 *
	 * @param string   $operation Description of the operation.
	 * @param callable $callback  The operation to execute.
	 * @param array    $context   Contextual information.
	 * @return mixed Operation result or WP_Error on failure.
	 */
	public function handle_network_operation(string $operation, callable $callback, array $context = []) {
		try {
			$result = $callback();

			// Check for WP_Error
			if (is_wp_error($result)) {
				$this->logger->error(
					"Network operation failed: {$operation}",
					array_merge($context, [
						'operation' => $operation,
						'error'     => $result->get_error_message(),
						'error_code' => $result->get_error_code(),
					])
				);
				return $result;
			}

			$this->logger->debug(
				"Network operation succeeded: {$operation}",
				array_merge($context, ['operation' => $operation])
			);

			return $result;

		} catch (Blitz_Cache_Network_Exception $e) {
			$this->logger->error(
				"Network operation failed: {$operation}",
				array_merge($context, [
					'operation' => $operation,
					'error'     => $e->getMessage(),
					'exception_context' => $e->get_context(),
				])
			);
			return new WP_Error('blitz_cache_network_error', $e->getMessage());

		} catch (Exception $e) {
			$this->logger->error(
				"Unexpected error in network operation: {$operation}",
				array_merge($context, [
					'operation' => $operation,
					'error'     => $e->getMessage(),
					'type'      => get_class($e),
				])
			);
			return new WP_Error('blitz_cache_error', $e->getMessage());
		}
	}

	/**
	 * Safely execute a callback and return a default value on failure.
	 *
	 * @param callable $callback     The callback to execute.
	 * @param mixed    $default      Default value on failure.
	 * @param string   $error_context Context for error logging.
	 * @return mixed Callback result or default value.
	 */
	public function safe_execute(callable $callback, $default = null, string $error_context = '') {
		try {
			$result = $callback();

			// Handle false returns for specific operations
			if ($result === false && $default !== false) {
				$this->logger->warning(
					"Safe execute returned false: {$error_context}",
					['context' => $error_context]
				);
				return $default;
			}

			return $result === null ? $default : $result;

		} catch (Exception $e) {
			$this->logger->error(
				"Safe execute failed: {$error_context}",
				[
					'context' => $error_context,
					'error'   => $e->getMessage(),
					'type'    => get_class($e),
				]
			);
			return $default;
		}
	}

	/**
	 * Handle JSON decode with error handling.
	 *
	 * @param string $json    JSON string to decode.
	 * @param bool   $assoc   Return as associative array.
	 * @param mixed  $default Default value on failure.
	 * @param string $context Context for error logging.
	 * @return mixed Decoded data or default value.
	 */
	public function safe_json_decode(string $json, bool $assoc = true, $default = [], string $context = '') {
		if (empty($json)) {
			return $default;
		}

		$data = json_decode($json, $assoc);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$this->logger->warning(
				'JSON decode failed',
				[
					'context' => $context,
					'error'   => json_last_error_msg(),
					'json_error_code' => json_last_error(),
				]
			);
			return $default;
		}

		return $data;
	}

	/**
	 * Handle JSON encode with error handling.
	 *
	 * @param mixed  $data    Data to encode.
	 * @param int    $flags   JSON flags.
	 * @param string $context Context for error logging.
	 * @return string|false JSON string or false on failure.
	 */
	public function safe_json_encode($data, int $flags = 0, string $context = '') {
		$json = wp_json_encode($data, $flags);

		if ($json === false) {
			$this->logger->error(
				'JSON encode failed',
				[
					'context' => $context,
					'data_type' => gettype($data),
				]
			);
			return false;
		}

		return $json;
	}

	/**
	 * Safely read a file.
	 *
	 * @param string $file_path Path to the file.
	 * @param string $context   Context for error logging.
	 * @return string|false File contents or false on failure.
	 */
	public function safe_file_read(string $file_path, string $context = '') {
		try {
			if (!file_exists($file_path)) {
				$this->logger->debug(
					"File not found: {$file_path}",
					['context' => $context, 'file' => $file_path]
				);
				return false;
			}

			if (!is_readable($file_path)) {
				$this->logger->error(
					"File not readable: {$file_path}",
					['context' => $context, 'file' => $file_path]
				);
				return false;
			}

			$content = @file_get_contents($file_path);

			if ($content === false) {
				$this->logger->error(
					"Failed to read file: {$file_path}",
					['context' => $context, 'file' => $file_path]
				);
				return false;
			}

			return $content;

		} catch (Exception $e) {
			$this->logger->error(
				"Exception reading file: {$file_path}",
				[
					'context' => $context,
					'file'    => $file_path,
					'error'   => $e->getMessage(),
				]
			);
			return false;
		}
	}

	/**
	 * Safely write to a file with atomic operation.
	 *
	 * @param string $file_path Path to the file.
	 * @param string $content   Content to write.
	 * @param string $context   Context for error logging.
	 * @return bool True on success, false on failure.
	 */
	public function safe_file_write(string $file_path, string $content, string $context = ''): bool {
		try {
			// Ensure directory exists
			$dir = dirname($file_path);
			if (!file_exists($dir)) {
				wp_mkdir_p($dir);
			}

			// Write to temp file first
			$temp_file = $file_path . '.tmp.' . uniqid();
			$result    = @file_put_contents($temp_file, $content, LOCK_EX);

			if ($result === false) {
				$this->logger->error(
					"Failed to write temp file: {$temp_file}",
					['context' => $context, 'target_file' => $file_path]
				);
				@unlink($temp_file);
				return false;
			}

			// Atomic rename
			if (!@rename($temp_file, $file_path)) {
				$this->logger->error(
					"Failed to rename temp file to target: {$file_path}",
					['context' => $context, 'temp_file' => $temp_file]
				);
				@unlink($temp_file);
				return false;
			}

			$this->logger->debug(
				"File written successfully: {$file_path}",
				['context' => $context, 'bytes' => strlen($content)]
			);

			return true;

		} catch (Exception $e) {
			$this->logger->error(
				"Exception writing file: {$file_path}",
				[
					'context' => $context,
					'error'   => $e->getMessage(),
				]
			);
			return false;
		}
	}

	/**
	 * Validate and sanitize a file path.
	 *
	 * @param string $path        Path to validate.
	 * @param string $allowed_dir Allowed base directory.
	 * @return bool True if path is valid and within allowed directory.
	 */
	public function validate_path(string $path, string $allowed_dir): bool {
		$real_path    = realpath($path);
		$real_allowed = realpath($allowed_dir);

		if ($real_path === false) {
			// File doesn't exist yet, check parent directory
			$parent = dirname($path);
			$real_parent = realpath($parent);

			if ($real_parent === false) {
				$this->logger->warning(
					'Path validation failed: parent directory does not exist',
					['path' => $path, 'parent' => $parent]
				);
				return false;
			}

			$real_path = $real_parent . '/' . basename($path);
		}

		if ($real_allowed === false) {
			$this->logger->error(
				'Path validation failed: allowed directory does not exist',
				['allowed_dir' => $allowed_dir]
			);
			return false;
		}

		// Normalize paths for comparison
		$real_path    = str_replace('\\', '/', $real_path);
		$real_allowed = str_replace('\\', '/', $real_allowed);

		// Ensure path starts with allowed directory
		if (strpos($real_path, $real_allowed) !== 0) {
			$this->logger->error(
				'Path validation failed: path outside allowed directory',
				[
					'path' => $path,
					'real_path' => $real_path,
					'allowed_dir' => $allowed_dir,
					'real_allowed' => $real_allowed,
				]
			);
			return false;
		}

		return true;
	}
}
