<?php
namespace Lucinda\Framework\STDERR;

/**
 * Defines blueprint for output following a routed exception.
 */
interface ErrorRenderer {
	/**
	 * Renders view to screen.
	 * 
	 * @param View $view
	 */
	function render(View $view);
}