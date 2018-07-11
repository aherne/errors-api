<?php
namespace Lucinda\MVC\STDERR;

class Request
{
    const DEFAULT_HTTP_STATUS = 500;
    const DEFAULT_ERROR_TYPE = ErrorType::NONE;
    
    private $exception;
    /**
     * @var Route
     */
    private $route;
    
    public function __construct(Application $application, $exception, $customContentType) {
        $this->setRoute($application, $exception, $customContentType);
        $this->setException($exception);
    }
    
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
    
    public function getRoute() {
        return $this->route;
    }
    
    private function setException($exception) {
        $this->exception = $exception;
    }
    
    public function getException() {
        return $this->exception;
    }
}

