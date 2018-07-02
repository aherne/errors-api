<?php
namespace Lucinda\Framework\STDERR;

/**
 * Defines blueprint for error display
 */
interface ErrorRenderer {
	/**
	 * Renders error to screen.
	 * 
	 * @param View $view
	 */
	function render(View $view);
}