<?php

namespace Test\Lucinda\STDERR\Service;

use Lucinda\STDERR\Service\ValidationFailedException;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;

class ValidationFailedExceptionTest
{
    public function inheritance()
    {
        $exception = new ValidationFailedException("validation failed");

        return [
            (new Objects($exception))->assertInstanceOf(\Exception::class),
            (new Strings($exception->getMessage()))->assertEquals("validation failed")
        ];
    }
}
