<?php
namespace Lucinda\Framework\STDERR;

/**
 * Defines blueprint for error reporting 
 */
interface ErrorReporter {
	/**
	 * Reports error to a storage medium.
	 * 
	 * @param \Exception|\Throwable $exception
     * @param integer $severity
	 */
	function report($exception, $severity);
}