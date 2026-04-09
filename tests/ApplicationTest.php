<?php

namespace Test\Lucinda\STDERR;

use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;
use Lucinda\STDERR\Application;
use Lucinda\STDERR\XmlTags\ResolverInfo;
use Lucinda\STDERR\XmlTags\RouteInfo;

class ApplicationTest
{
    private Application $object;

    public function __construct()
    {
        $this->object = new Application(__DIR__."/fixtures/root.xml");
    }

    public function getApplicationInfo()
    {
        $applicationInfo = $this->object->getApplicationInfo();

        return [
            (new Strings($applicationInfo->getDefaultRoute()))->assertEquals("default"),
            (new Strings($applicationInfo->getDefaultFormat()))->assertEquals("txt"),
            (new Strings($applicationInfo->getViewsFolder()))->assertEquals("tests/fixtures/views")
        ];
    }

    public function getResolvers()
    {
        return (new Objects($this->object->getResolvers("txt")))->assertInstanceOf(ResolverInfo::class);
    }

    public function getRoutes()
    {
        return (new Objects($this->object->getRoutes("default")))->assertInstanceOf(RouteInfo::class);
    }
}
