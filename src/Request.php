<?php
namespace Lucinda\MVC\STDERR;

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
     * @param \Exception $exception Error "request" that fed STDERR stream
     */
    public function __construct(Route $route, $exception)
    {
        $this->setRoute($route);
        $this->setException($exception);
    }

    /**
     * Detects route based on exception handled.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     */
    private function setRoute(Route $route)
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
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Sets exception handled
     *
     * @param \Exception $exception Error "request" that fed STDERR stream
     */
    private function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * Gets exception handled
     *
     * @return \Exception $exception Error "request" that fed STDERR stream
     */
    public function getException()
    {
        return $this->exception;
    }
}
