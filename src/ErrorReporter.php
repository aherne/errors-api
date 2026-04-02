<?php

namespace Lucinda\STDERR;

interface ErrorReporter
{
    public function report(\Throwable $error, ?\Throwable $previous = null);
}