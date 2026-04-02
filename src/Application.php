<?php

namespace Lucinda\STDERR;

use Lucinda\MVC\XmlTagsLists\ResolversList;
use Lucinda\MVC\XmlTagsLists\RoutesList;
use Lucinda\STDERR\XmlTags\ResolverInfo;
use Lucinda\STDERR\XmlTags\RouteInfo;

/**
 * Detects settings necessary to configure MVC Errors API based on contents of XML file and development environment:
 * - default content types of rendered response
 * - location of controllers that map exceptions thrown
 * - location of views that map exceptions thrown
 * - possible objects to use in reporting error to
 * - possible objects to use in rendering response
 * - possible routes that map controllers/views to exception
 */
class Application extends \Lucinda\MVC\Application
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Application::setRoutes()
     */
    protected function setRoutes(): void
    {
        $list = new RoutesList(RouteInfo::class);
        $this->routes = $list->convert($this->reader->getTag("routes"));
    }

     /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Application::setResolvers()
     */
    protected function setResolvers(): void
    {
        $list = new ResolversList(ResolverInfo::class);
        $this->formats = $list->convert($this->reader->getTag("resolvers"));
    }
}
