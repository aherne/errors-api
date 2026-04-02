<?php

namespace Lucinda\STDERR;

/**
 * Blueprint for handling an Throwable/Exception that got original client's request go to STDERR
 */
interface FatalErrorHandler
{
    public function handleFatal(\Throwable $exception, ?\Throwable $previous = null): void;
}
