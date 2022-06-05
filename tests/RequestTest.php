<?php

namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\Request;
use Lucinda\STDERR\Application\Route;
use Lucinda\UnitTest\Result;

class RequestTest
{
    private $object;
    private $route;
    private $exception;

    public function __construct()
    {
        $this->route = new Route(
            simplexml_load_string(
                '
        <exception class="Lucinda\MVC\STDOUT\PathNotFoundException" controller="PathNotFoundController" http_status="404" error_type="CLIENT" view="404"/>
        '
            )
        );
        $this->exception = new \Exception("asd");
        $this->object = new Request($this->route, $this->exception);
    }

    public function getRoute()
    {
        return new Result($this->object->getRoute()==$this->route);
    }


    public function getException()
    {
        return new Result($this->object->getException()==$this->exception);
    }
}
