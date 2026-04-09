<?php

namespace Test\Lucinda\STDERR\Support;

use Lucinda\STDERR\FatalErrorResolver;

class RecordingFatalErrorResolver implements FatalErrorResolver
{
    private int $count = 0;
    private ?\Throwable $lastException = null;
    private ?\Throwable $lastPrevious = null;

    public function resolve(\Throwable $exception, ?\Throwable $previous = null): string
    {
        $this->count++;
        $this->lastException = $exception;
        $this->lastPrevious = $previous;

        return "";
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getLastException(): ?\Throwable
    {
        return $this->lastException;
    }

    public function getLastPrevious(): ?\Throwable
    {
        return $this->lastPrevious;
    }
}
