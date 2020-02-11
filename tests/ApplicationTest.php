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
        return new Result($this->object->getControllersPath()=="mocks/controllers");
    }
        

    public function getReportersPath()
    {
        return new Result($this->object->getReportersPath()=="mocks/reporters");
    }
        

    public function getViewResolversPath()
    {
        return new Result($this->object->getViewResolversPath()=="mocks/resolvers");
    }
        

    public function getViewsPath()
    {
        return new Result($this->object->getViewsPath()=="mocks/views");
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
        

    public function formats()
    {
        return new Result($this->object->formats("html")!==null);
    }
        

    public function routes()
    {
        return new Result($this->object->routes("PathNotFoundException")!==null);
    }
        

    public function getTag()
    {
        return new Result($this->object->getTag("reporters")!==null);
    }
        

}
