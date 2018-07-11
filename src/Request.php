<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates a STDERR request by matching routes @ XML with exception handled
 */
class Request
{
    const DEFAULT_HTTP_STATUS = 500;
    const DEFAULT_ERROR_TYPE = ErrorType::NONE;

    private $exception;
    private $route;

    /**
     * Detects route based on exception handled.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param \Error|\Exception $exception Error "request" that fed STDERR stream
     * @param string $customContentType Content type of rendered response specifically signalled to FrontController.
     */
    public function __construct(Application $application, $exception, $customContentType) {
        $this->setRoute($application, $exception, $customContentType);
        $this->setException($exception);
    }

    /**
     * Detects route based on exception handled.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param \Error|\Exception $exception Error "request" that fed STDERR stream
     * @param string $customContentType Content type of rendered response specifically signalled to FrontController.
     */
    private function setRoute(Application $application, $exception, $customContentType) {
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
        if(!$this->route->getContentType()) {
            $this->route->setContentType($customContentType?$customContentType:$application->getDefaultContentType());
        }
        if(!$this->route->getErrorType()) {
            $this->route->setErrorType(self::DEFAULT_ERROR_TYPE);
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
     * @param \Error|\Exception $exception Error "request" that fed STDERR stream
     */
    private function setException($exception) {
        $this->exception = $exception;
    }

    /**
     * Gets exception handled
     *
     * @return \Error|\Exception $exception Error "request" that fed STDERR stream
     */
    public function getException() {
        return $this->exception;
    }
}

