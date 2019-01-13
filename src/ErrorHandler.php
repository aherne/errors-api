<?php
namespace Lucinda\MVC\STDERR;

/**
 * Blueprint for handling an Throwable/Exception that got original client's request go to STDERR
 */
interface ErrorHandler
{
    /**
     * Handles errors by delegating to registered storage mediums (if any) then output using display method (if any)
     *
     * @param \Exception|\Throwable $exception Encapsulates error information.
     */
    function handle($exception);
}

