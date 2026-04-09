<?php

namespace Test\Lucinda\STDERR\Service;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\Service\ValidatedRequest;
use Lucinda\UnitTest\Validator\Strings;
use Test\Lucinda\STDERR\Support\FixtureException;
use Test\Lucinda\STDERR\Support\NotFoundException;

class ValidatedRequestTest
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application(dirname(__DIR__)."/fixtures/root.xml");
    }

    public function getRoute()
    {
        $matched = new ValidatedRequest($this->application, new NotFoundException("missing"), "");
        $fallback = new ValidatedRequest($this->application, new FixtureException("fallback"), "");

        return [
            (new Strings($matched->getRoute()))->assertEquals(NotFoundException::class),
            (new Strings($fallback->getRoute()))->assertEquals("default")
        ];
    }

    public function getFormat()
    {
        $override = new ValidatedRequest($this->application, new FixtureException("override"), "json");
        $fallback = new ValidatedRequest($this->application, new FixtureException("default"), "xml");

        return [
            (new Strings($override->getFormat()))->assertEquals("json"),
            (new Strings($fallback->getFormat()))->assertEquals("txt")
        ];
    }
}
