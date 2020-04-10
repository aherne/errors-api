<?php
namespace Test\Lucinda\STDERR\Locators;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\Request;
use Lucinda\STDERR\Locators\ControllerLocator;
use Lucinda\UnitTest\Result;
use Test\Lucinda\STDERR\PathNotFoundException;

class ControllerLocatorTest
{
    public function getClassName()
    {
        $result = [];
        
        $application = new Application(dirname(__DIR__)."/configuration.xml", "local");
        
        $locator = new ControllerLocator(
            $application,
            new Request($application->routes()[""], new \Exception("asd"))
        );
        $result[] = new Result($locator->getClassName()===null, "tested default controller");
        
        $locator = new ControllerLocator(
            $application,
            new Request($application->routes()["Test\Lucinda\STDERR\PathNotFoundException"], new PathNotFoundException("asd"))
        );
        $result[] = new Result($locator->getClassName()==="PathNotFoundController", "tested exception controller");
        
        return $result;
    }
}
