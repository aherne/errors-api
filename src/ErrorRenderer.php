<?php
namespace Lucinda\Framework\STDERR;

/**
 * Defines blueprint for error display
 */
interface ErrorRenderer {
	/**
	 * Renders error to screen.
	 * 
	 * @param \Exception|\Throwable $exception
	 */
	function render($exception);
}