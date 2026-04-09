<?php

namespace Test\Lucinda\STDERR\XmlTags;

use Lucinda\MVC\XmlReader\Element;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\STDERR\ErrorType;
use Lucinda\STDERR\XmlTags\RouteInfo;
use Lucinda\UnitTest\Validator\Integers;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;

class RouteInfoTest
{
    private RouteInfo $object;

    public function __construct()
    {
        $this->object = new RouteInfo(
            new Element(
                simplexml_load_string(
                    '<route id="default" view="default" http_status="404" error_type="CLIENT" exit_code="4"/>'
                )
            )
        );
    }

    public function getHttpStatus()
    {
        return [
            (new Objects($this->object->getHttpStatus()))->assertInstanceOf(HttpStatus::class),
            (new Integers($this->object->getHttpStatus()->value))->assertEquals(404)
        ];
    }

    public function getErrorType()
    {
        return [
            (new Objects($this->object->getErrorType()))->assertInstanceOf(ErrorType::class),
            (new Strings($this->object->getErrorType()->value))->assertEquals("CLIENT")
        ];
    }

    public function getExitCode()
    {
        return (new Integers($this->object->getExitCode()))->assertEquals(4);
    }
}
