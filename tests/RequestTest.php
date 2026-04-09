<?php

namespace Test\Lucinda\STDERR;

use Lucinda\MVC\XmlReader\Element;
use Lucinda\STDERR\Request;
use Lucinda\STDERR\XmlTags\RouteInfo;
use Lucinda\UnitTest\Validator\Integers;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;
use Test\Lucinda\STDERR\Support\FixtureException;

class RequestTest
{
    private Request $object;
    private RouteInfo $route;
    private FixtureException $exception;

    public function __construct()
    {
        $this->route = new RouteInfo(
            new Element(
                simplexml_load_string('<route id="default" view="default" http_status="500" error_type="LOGICAL" exit_code="7"/>')
            )
        );
        $this->exception = new FixtureException("fixture");
        $this->object = new Request($this->route, $this->exception);
    }

    public function getRoute()
    {
        return [
            (new Objects($this->object->getRoute()))->assertInstanceOf(RouteInfo::class),
            (new Strings($this->object->getRoute()->getID()))->assertEquals("default"),
            (new Integers(spl_object_id($this->object->getRoute())))->assertEquals(spl_object_id($this->route))
        ];
    }

    public function getException()
    {
        return [
            (new Objects($this->object->getException()))->assertInstanceOf(\Throwable::class),
            (new Strings($this->object->getException()->getMessage()))->assertEquals("fixture"),
            (new Integers(spl_object_id($this->object->getException())))->assertEquals(spl_object_id($this->exception))
        ];
    }
}
