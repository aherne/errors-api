<?php
namespace Test\Lucinda\STDERR\Locators;
    
use Lucinda\STDERR\Locators\ClassFinder;
use Lucinda\UnitTest\Result;

class ClassFinderTest
{

    public function find()
    {
        $finder = new ClassFinder(dirname(__DIR__));
        return new Result($finder->find("Test\Lucinda\STDERR\MockEmergencyHandler")=="Test\Lucinda\STDERR\MockEmergencyHandler");
    }
        

}
