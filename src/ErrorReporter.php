<?php
namespace Lucinda\MVC\STDERR;

/**
 * Defines blueprint for reporting an exception that fed STDERR flow to a storage medium (eg: log file)
 */
interface ErrorReporter
{
    /**
     * Reports error info to a storage medium.
     *
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     */
    public function report(Request $request);
}
