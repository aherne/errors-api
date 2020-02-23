<?php
namespace Test\Lucinda\STDERR\Locators;

use Lucinda\STDERR\Locators\ReporterLocator;
use Lucinda\STDERR\Application;
use Lucinda\UnitTest\Result;

class ReporterLocatorTest
{
    public function getClassName()
    {
        $locator = new ReporterLocator(new Application(dirname(__DIR__)."/configuration.xml", "local"), "FileReporter");
        return new Result($locator->getClassName()=="FileReporter");
    }
}
