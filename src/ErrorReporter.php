<?php

namespace Lucinda\STDERR;

interface ErrorReporter
{
    public function report(ErrorType $errorType, \Throwable $error, ?\Throwable $previous = null): void;
}