<?php
namespace Lucinda\MVC\STDERR;

/**
 * Defines blueprint for rendering a response back to caller after an exception fed STDERR
 */
interface ErrorRenderer {
	/**
	 * Renders response to screen.
	 *
     * @param Response $response Encapsulates response to send back to caller.
	 */
	function render(Response $response);
}