<?php
namespace Test\Lucinda\STDERR\Locators;

use Lucinda\STDERR\Locators\ViewResolverLocator;
use Lucinda\STDERR\Application;
use Lucinda\UnitTest\Result;

class ViewResolverLocatorTest
{
    public function getClassName()
    {
        $application = new Application(dirname(__DIR__)."/configuration.xml", "local");
        $locator = new ViewResolverLocator($application, $application->resolvers("html"));
        return new Result($locator->getClassName()=="HtmlRenderer");
    }
}
