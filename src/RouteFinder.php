<?php
namespace Lucinda\Framework\STDERR;

require_once("Route.php");
require_once("ErrorType.php");

/**
 * Locates route in XML based on exception thrown.
 */
class RouteFinder {
    const DEFAULT_HTTP_STATUS = 500;
    const DEFAULT_ERROR_TYPE = ErrorType::NONE;

    private $route;

    /**
     * RouteFinder constructor.
     *
     * @param Application $application
     * @param \Exception|\Error $exception
     * @param string $customContentType
     */
    public function __construct(Application $application, $exception, $customContentType) {
        $this->setRoute($application, $exception);
        $this->setDefaults($application, $customContentType);
    }

    /**
     * Locates route from XML exceptions tag or latter's exception tag child.
     *
     * @param Application $application
     * @param \Exception|\Error $exception
     */
    private function setRoute(Application $application, $exception) {
        // get default route
        $this->route = $this->compileRoute($application->getXML()->exceptions);
        
        // override with specific route, if set
        $tmp = (array) $application->getXML()->exceptions;
        if(empty($tmp["exception"])) return;
        $tmp = $tmp["exception"];
        if(!is_array($tmp)) $tmp = array($tmp);
        $targetClassName = get_class($exception);
        foreach($tmp as $info) {
            $currentClassName = (string) $info['class'];
            if($currentClassName!=$targetClassName) continue;
            $this->route = $this->compileRoute($info);
        }
    }

    /**
     * Overrides values that couldn't be detected with defaults.
     *
     * @param Application $application
     * @param string $customContentType
     */
    private function setDefaults(Application $application, $customContentType) {
        // override non-existent http status with default
        if(!$this->route->getHttpStatus()) {
            $this->route->setHttpStatus(self::DEFAULT_HTTP_STATUS);
        }
        
        // override non-existent content type with defaults
        if(!$this->route->getContentType()) {
            $this->route->setContentType($customContentType?$customContentType:$application->getDefaultContentType());
        }
        
        // override non-existent reporting status with default
        if(!$this->route->getErrorType()) {
            $this->route->setErrorType(self::DEFAULT_ERROR_TYPE);
        }
    }

    /**
     * Compiles a route based on XML exception/exceptions tag properties.
     *
     * @param \SimpleXMLElement $info
     * @return Route
     */
    private function compileRoute(\SimpleXMLElement $info) {
        $route = new Route();
        $route->setController((string) $info["controller"]);
        $route->setView((string) $info["view"]);
        $route->setHttpStatus((string) $info["http_status"]);
        $route->setErrorType((string) $info["error_type"]);
        $route->setContentType((string) $info["content_type"]);
        return $route;        
    }

    /**
     * Gets route detected.
     *
     * @return Route
     */
    public function getRoute() {
        return $this->route;
    }
}