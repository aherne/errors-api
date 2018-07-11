<?php
namespace Lucinda\MVC\STDERR;

require_once("Route.php");
require_once("ErrorType.php");

/**
 * Locates route in XML based on exception thrown.
 */
class RoutesFinder {
    private $routes = array();

    /**
     * RouteFinder constructor.
     *
     * @param Application $application
     */
    public function __construct(\SimpleXMLElement $xml) {
        $this->setRoutes($xml);
    }

    /**
     * Locates route from XML exceptions tag or latter's exception tag child.
     *
     * @param Application $application
     */
    private function setRoutes(\SimpleXMLElement $xml) {
        // get default route
        $this->routes[""] = $this->compileRoute($xml);
        
        // override with specific route, if set
        $tmp = (array) $xml;
        if(empty($tmp["exception"])) return;
        $tmp = $tmp["exception"];
        if(!is_array($tmp)) $tmp = array($tmp);
        foreach($tmp as $info) {
            $currentClassName = (string) $info['class'];
            if(!$currentClassName) throw new Exception("Exception class not defined!");
            $this->routes[$currentClassName] = $this->compileRoute($info);
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
     * Gets routes detected.
     *
     * @return Route[string]
     */
    public function getRoutes() {
        return $this->routes;
    }
}