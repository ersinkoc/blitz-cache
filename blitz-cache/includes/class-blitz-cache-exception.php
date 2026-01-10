<?php
/**
 * Custom Exception Classes for Blitz Cache
 *
 * Provides a hierarchy of exception types for better error handling
 * and categorization of different failure scenarios.
 *
 * @package Blitz_Cache
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for all Blitz Cache exceptions
 *
 * Ensures all exceptions can provide contextual information.
 */
interface Blitz_Cache_Exception_Interface {

	/**
	 * Get contextual information about the exception.
	 *
	 * @return array Contextual data.
	 */
	public function get_context(): array;
}

/**
 * Base exception class for Blitz Cache
 *
 * All custom exceptions extend this class to provide
 * consistent error handling and context tracking.
 */
class Blitz_Cache_Exception extends Exception implements Blitz_Cache_Exception_Interface {

	/**
	 * Contextual information about the exception.
	 *
	 * @var array
	 */
	protected array $context = [];

	/**
	 * Constructor.
	 *
	 * @param string     $message  Error message.
	 * @param array      $context  Contextual information.
	 * @param int        $code     Error code.
	 * @param Throwable $previous Previous exception.
	 */
	public function __construct(string $message, array $context = [], int $code = 0, ?Throwable $previous = null) {
		$this->context = $context;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get contextual information about the exception.
	 *
	 * @return array Contextual data.
	 */
	public function get_context(): array {
		return $this->context;
	}
}

/**
 * File operation exceptions
 *
 * Thrown when file read/write/delete operations fail.
 */
class Blitz_Cache_File_Exception extends Blitz_Cache_Exception {}

/**
 * Permission exceptions
 *
 * Thrown when directory/file permissions prevent operations.
 */
class Blitz_Cache_Permission_Exception extends Blitz_Cache_Exception {}

/**
 * Network/API exceptions
 *
 * Thrown when external API calls fail (Cloudflare, etc.).
 */
class Blitz_Cache_Network_Exception extends Blitz_Cache_Exception {}

/**
 * Validation exceptions
 *
 * Thrown when input validation fails.
 */
class Blitz_Cache_Validation_Exception extends Blitz_Cache_Exception {}
