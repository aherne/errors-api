<?php

namespace Lucinda\STDERR;

use Lucinda\MVC\Response\Console;

final class ConsoleResponse extends Console
{
    public function __construct(int $exitCode)
    {
        $this->setExitCode($exitCode);
    }

    protected function emit(string $body): void
    {
        if ($body) {
            fwrite(STDERR, $body);
        }
    }
}