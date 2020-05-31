<?php
namespace Lucinda\STDERR;

use Lucinda\STDERR\Application\Route;

/**
 * Encapsulates a STDERR request by matching routes @ XML with exception handled
 */
class Request
{
    const DEFAULT_HTTP_STATUS = 500;

    private $exception;
    private $route;

    /**
     * Detects route based on exception handled.
     *
     * @param Route $route Matching route information detected from XML
     * @param \Throwable $exception Error "request" that fed STDERR stream
     */
    public function __construct(Route $route, \Throwable $exception)
    {
        $this->setRoute($route);
        $this->setException($exception);
    }

    /**
     * Detects route based on exception handled.
     *
     * @param Route $route
     */
    private function setRoute(Route $route): void
    {
        $this->route = $route;
        // override non-existent properties with defaults
        if (!$this->route->getHttpStatus()) {
            $this->route->setHttpStatus(self::DEFAULT_HTTP_STATUS);
        }
    }

    /**
     * Gets route that matches exception received.
     *
     * @return Route
     */
    public function getRoute(): Route
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
