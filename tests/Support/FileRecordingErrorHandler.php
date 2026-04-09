<?php

namespace Test\Lucinda\STDERR\Support;

use Lucinda\STDERR\ErrorHandler;

class FileRecordingErrorHandler implements ErrorHandler
{
    public function __construct(private string $outputFile)
    {
    }

    public function handle(\Throwable $exception): void
    {
        file_put_contents(
            $this->outputFile,
            json_encode([
                "mode" => "handle",
                "class" => $exception::class,
                "message" => $exception->getMessage()
            ], JSON_THROW_ON_ERROR)
        );
    }

    public function handleFatal(\Throwable $exception, ?\Throwable $previous = null): void
    {
        file_put_contents(
            $this->outputFile,
            json_encode([
                "mode" => "fatal",
                "class" => $exception::class,
                "message" => $exception->getMessage(),
                "previous" => $previous?->getMessage()
            ], JSON_THROW_ON_ERROR)
        );
    }
}
