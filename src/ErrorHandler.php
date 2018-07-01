<?php
namespace Lucinda\Framework\STDERR;

/**
 * Blueprint for handling an error (user-defined or system exception, incl. PHP errors)
 */
interface ErrorHandler
{
    /**
     * Handles errors by delegating to registered storage mediums (if any) then output using display method (if any)
     *
     * @param \Exception|\Throwable $e Encapsulates error information.
     */
    function handle($exception);
}

