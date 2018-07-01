<?php
namespace Lucinda\Framework\STDERR;

require_once("Route.php");

class RouteFinder {
    const DEFAULT_HTTP_STATUS = 500;
    const DEFAULT_REPORTING_STATUS = LOG_ERR;
    private $route;
    
    public function __construct(Application $application, $exception, $customContentType) {
        $this->setRoute($application, $exception);
        $this->setDefaults($application, $customContentType);
    }
    
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
        if(!$this->route->getReportingStatus()) {
            $this->route->setReportingStatus(self::DEFAULT_REPORTING_STATUS);
        }
    }
    
    private function compileRoute(\SimpleXMLElement $info) {
        $route = new Route();
        $route->setController((string) $info["controller"]);
        $route->setView((string) $info["view"]);
        $route->setHttpStatus((string) $info["http_status"]);
        $route->setReportingStatus((string) $info["reporting_status"]);
        $route->setContentType((string) $info["content_type"]);
        return $route;        
    }
    
    public function getRoute() {
        return $this->route;
    }
}