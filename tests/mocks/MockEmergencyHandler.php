<?php

namespace Test\Lucinda\STDERR\mocks;

use Lucinda\STDERR\ErrorHandler;

class MockEmergencyHandler implements ErrorHandler
{
    public function handle(\Throwable $exception): void
    {
        var_dump($exception->getTraceAsString());
        die($exception->getMessage());
    }
}
