<?php
namespace Lucinda\MVC\STDERR;

/**
 * Defines blueprint for output following a routed exception.
 */
interface ErrorRenderer {
	/**
	 * Renders view to screen.
	 * 
	 * @param Response $view
	 */
	function render(Response $view);
}