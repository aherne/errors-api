<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates a STDERR request by matching routes @ XML with exception handled
 */
class Request
{
    const DEFAULT_HTTP_STATUS = "500 Internal Server Error";

    private $exception;
    private $route;

    /**
     * Detects route based on exception handled.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param \Exception $exception Error "request" that fed STDERR stream
     */
    public function __construct(Application $application, $exception) {
        $this->setRoute($application, $exception);
        $this->setException($exception);
    }

    /**
     * Detects route based on exception handled.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param \Exception $exception Error "request" that fed STDERR stream
     */
    private function setRoute(Application $application, $exception) {
        $routes = $application->getRoutes();
        $targetClass = get_class($exception);
        
        // detects route
        $this->route = $routes[""];
        foreach($routes as $currentClass=>$route) {
            if($currentClass == $targetClass) {
                $this->route = $route;
            }
        }
        // override non-existent properties with defaults
        if(!$this->route->getHttpStatus()) {
            $this->route->setHttpStatus(self::DEFAULT_HTTP_STATUS);
        }
    }

    /**
     * Gets route that matches exception received.
     *
     * @return Route
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * Sets exception handled
     *
     * @param \Exception $exception Error "request" that fed STDERR stream
     */
    private function setException($exception) {
        $this->exception = $exception;
    }

    /**
     * Gets exception handled
     *
     * @return \Exception $exception Error "request" that fed STDERR stream
     */
    public function getException() {
        return $this->exception;
    }
}

