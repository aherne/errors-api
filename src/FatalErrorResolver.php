<?php

namespace Lucinda\STDERR;

/**
 * Blueprint for handling an Throwable/Exception that got original client's request go to STDERR
 */
interface FatalErrorResolver
{
    public function resolve(\Throwable $exception, ?\Throwable $previous = null): string;
}
