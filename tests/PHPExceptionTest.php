<?php

namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\PHPException;
use Lucinda\UnitTest\Result;
use Test\Lucinda\STDERR\mocks\MockEmergencyHandler;

class PHPExceptionTest
{
    private $handler;

    public function __construct()
    {
        error_reporting(E_ALL);
        set_error_handler('\\Lucinda\\STDERR\\PHPException::nonFatalError', E_ALL);
        register_shutdown_function('\\Lucinda\\STDERR\\PHPException::fatalError');
        $this->handler = new MockEmergencyHandler();
    }

    public function setErrorHandler()
    {
        PHPException::setErrorHandler($this->handler);
        return new Result(true);
    }


    public function getErrorHandler()
    {
        return new Result(PHPException::getErrorHandler()==$this->handler);
    }


    public function nonFatalError()
    {
        return new Result(false, "PHP non-fatal errors cannot be unit tested!");
    }


    public function fatalError()
    {
        return new Result(false, "PHP fatal errors cannot be unit tested!");
    }
}
