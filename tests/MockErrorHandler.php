<?php
namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\ErrorHandler;

class MockErrorHandler implements ErrorHandler
{
    public function handle(\Throwable $exception): void
    {
        
    }
}

