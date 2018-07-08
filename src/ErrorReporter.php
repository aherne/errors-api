<?php
namespace Lucinda\Framework\STDERR;

/**
 * Defines blueprint for reporting a routed exception
 */
interface ErrorReporter {
	/**
	 * Reports error info to a storage medium.
	 * 
	 * @param Request $request
	 */
    function report($request);
}