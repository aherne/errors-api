<?php

namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\Application;
use Lucinda\UnitTest\Result;

class ApplicationTest
{
    private $object;

    public function __construct()
    {
        $this->object = new Application(__DIR__."/mocks/configuration.xml", "local");
    }

    public function getDefaultFormat()
    {
        return new Result($this->object->getDefaultFormat()=="html");
    }

    public function getViewsPath()
    {
        return new Result($this->object->getViewsPath()=="tests/mocks/views");
    }


    public function getDisplayErrors()
    {
        return new Result($this->object->getDisplayErrors());
    }


    public function getVersion()
    {
        return new Result($this->object->getVersion()=="1.0.0");
    }


    public function reporters()
    {
        return new Result($this->object->reporters("Test\Lucinda\STDERR\mocks\Reporters\File")!==null);
    }


    public function resolvers()
    {
        return new Result($this->object->resolvers("html")!==null);
    }


    public function routes()
    {
        return new Result($this->object->routes("Test\Lucinda\STDERR\mocks\PathNotFoundException")!==null);
    }


    public function getTag()
    {
        return new Result($this->object->getTag("reporters")!==null);
    }

    public function getXML()
    {
        return new Result($this->object->getXML() instanceof \SimpleXMLElement);
    }
}
