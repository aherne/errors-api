<?php

namespace Test\Lucinda\STDERR\Support;

use Lucinda\STDERR\DisplayErrors;

class RecordingDisplayErrors implements DisplayErrors
{
    public function __construct(private bool $displayErrors = false)
    {
    }

    public function shouldDisplayErrors(): bool
    {
        return $this->displayErrors;
    }
}
