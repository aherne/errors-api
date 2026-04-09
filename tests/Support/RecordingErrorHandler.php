<?php

namespace Test\Lucinda\STDERR\Support;

use Lucinda\STDERR\ErrorHandler;

class RecordingErrorHandler implements ErrorHandler
{
    private int $handleCount = 0;
    private int $fatalCount = 0;
    private ?\Throwable $lastHandled = null;
    private ?\Throwable $lastFatal = null;
    private ?\Throwable $lastPrevious = null;

    public function handle(\Throwable $exception): void
    {
        $this->handleCount++;
        $this->lastHandled = $exception;
    }

    public function handleFatal(\Throwable $exception, ?\Throwable $previous = null): void
    {
        $this->fatalCount++;
        $this->lastFatal = $exception;
        $this->lastPrevious = $previous;
    }

    public function getHandleCount(): int
    {
        return $this->handleCount;
    }

    public function getFatalCount(): int
    {
        return $this->fatalCount;
    }

    public function getLastHandled(): ?\Throwable
    {
        return $this->lastHandled;
    }

    public function getLastFatal(): ?\Throwable
    {
        return $this->lastFatal;
    }

    public function getLastPrevious(): ?\Throwable
    {
        return $this->lastPrevious;
    }
}
