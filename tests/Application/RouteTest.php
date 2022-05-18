<?php

namespace Test\Lucinda\STDERR\Application;

use Lucinda\STDERR\Application\Route;
use Lucinda\UnitTest\Result;
use Lucinda\STDERR\ErrorType;

class RouteTest
{
    private $object;

    public function __construct()
    {
        $this->object = new Route(simplexml_load_string('
        <route id="Lucinda\MVC\STDOUT\PathNotFoundException" controller="PathNotFoundController" http_status="404" error_type="CLIENT" view="404"/>
        '));
    }

    public function getController()
    {
        return new Result($this->object->getController()=="PathNotFoundController");
    }


    public function getView()
    {
        return new Result($this->object->getView()=="404");
    }


    public function getHttpStatus()
    {
        return new Result($this->object->getHttpStatus()->value == "404 Not Found");
    }


    public function getErrorType()
    {
        return new Result($this->object->getErrorType()->value == "CLIENT");
    }
}
