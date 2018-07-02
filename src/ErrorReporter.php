<?php
namespace Lucinda\Framework\STDERR;

/**
 * Defines blueprint for reporting a routed exception
 */
interface ErrorReporter {
	/**
	 * Reports error to a storage medium.
	 * 
	 * @param \Exception|\Throwable $exception
     * @param ErrorType $type One of possible error types
	 */
	function report($exception, $type);
}