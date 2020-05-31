<?php
namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\Application;
use Lucinda\UnitTest\Result;

class ApplicationTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new Application(__DIR__."/configuration.xml", "local");
    }

    public function getDefaultFormat()
    {
        return new Result($this->object->getDefaultFormat()=="html");
    }
        

    public function getControllersPath()
    {
        return new Result($this->object->getControllersPath()=="tests/mocks/controllers");
    }
        

    public function getReportersPath()
    {
        return new Result($this->object->getReportersPath()=="tests/mocks/reporters");
    }
        

    public function getViewResolversPath()
    {
        return new Result($this->object->getViewResolversPath()=="tests/mocks/resolvers");
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
        return new Result($this->object->reporters("FileReporter")!==null);
    }
        

    public function resolvers()
    {
        return new Result($this->object->resolvers("html")!==null);
    }
        

    public function routes()
    {
        return new Result($this->object->routes("Test\Lucinda\STDERR\PathNotFoundException")!==null);
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
