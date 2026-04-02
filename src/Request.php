<?php

namespace Lucinda\STDERR;

use Lucinda\MVC\Facet;
use Lucinda\STDERR\XmlTags\RouteInfo;

/**
 * Encapsulates a STDERR request by matching routes @ XML with exception handled
 */
class Request implements Facet
{
    private RouteInfo $route;
    private \Throwable $exception;

    /**
     * Detects route based on exception handled.
     *
     * @param RouteInfo      $route     Matching route information detected from XML
     * @param \Throwable $exception Error "request" that fed STDERR stream
     */
    public function __construct(RouteInfo $route, \Throwable $exception)
    {
        $this->setRoute($route);
        $this->setException($exception);
    }

    /**
     * Detects route based on exception handled.
     *
     * @param RouteInfo $route
     */
    private function setRoute(RouteInfo $route): void
    {
        $this->route = $route;
    }

    /**
     * Gets route that matches exception received.
     *
     * @return RouteInfo
     */
    public function getRoute(): RouteInfo
    {
        return $this->route;
    }

    /**
     * Sets exception handled
     *
     * @param \Throwable $exception Error "request" that fed STDERR stream
     */
    private function setException(\Throwable $exception): void
    {
        $this->exception = $exception;
    }

    /**
     * Gets exception handled
     *
     * @return \Throwable $exception Error "request" that fed STDERR stream
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
