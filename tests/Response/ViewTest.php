<?php
namespace Test\Lucinda\STDERR\Response;
    
use Lucinda\STDERR\Response\View;
use Lucinda\UnitTest\Result;

class ViewTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new View("index");
    }

    public function setFile()
    {
        $this->object->setFile("admin");
        return new Result(true);
    }
        

    public function getFile()
    {
        return new Result($this->object->getFile()=="admin");
    }
        

    public function data()
    {
        $this->object->data("test", "me");
        return new Result($this->object->data("test")=="me");
    }
        

}
