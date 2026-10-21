<?php

namespace Test\Lucinda\STDERR\Support;

use Lucinda\STDERR\ErrorReporter;
use Lucinda\STDERR\ErrorType;

class RecordingReporter implements ErrorReporter
{
    /**
     * @var array<int,array{error:\Throwable,previous:? \Throwable}>
     */
    private array $entries = [];

    public function report(ErrorType $errorType, \Throwable $error, ?\Throwable $previous = null): void
    {
        $this->entries[] = [
            "error" => $error,
            "previous" => $previous
        ];
    }

    public function getCount(): int
    {
        return count($this->entries);
    }

    public function getLastError(): ?\Throwable
    {
        return $this->entries ? $this->entries[array_key_last($this->entries)]["error"] : null;
    }

    public function getLastPrevious(): ?\Throwable
    {
        return $this->entries ? $this->entries[array_key_last($this->entries)]["previous"] : null;
    }
}
