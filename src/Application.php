<?php
namespace Lucinda\MVC\STDERR;

require_once("application/ErrorReportersFinder.php");
require_once("application/ErrorRenderersFinder.php");
require_once("application/RoutesFinder.php");

/**
 * Detects settings necessary to configure MVC Errors API based on contents of XML file and development environment:
 * - default content types of rendered response
 * - location of controllers that map exceptions thrown
 * - location of views that map exceptions thrown
 * - possible objects to use in reporting error to
 * - possible objects to use in rendering response
 * - possible routes that map controllers/views to exception
 */
class Application
{
    private $includePath;
    private $simpleXMLElement;
    private $controllersPath, $viewsPath, $reportersPath, $renderersPath, $publicPath, $defaultContentType, $version;
    private $reporters=array(), $renderers=array(), $routes=array();
    private $displayErrors=false;
    
    /**
     * Performs detection process.
     *
     * @param string $xmlPath Relative location of XML file containing settings.
     * @param string $developmentEnvironment Development environment server is running into (eg: local, dev, live)
     * @param string $includePath Absolute root path where reporters / renderers / controllers / views should be located
     * @throws Exception If XML is misconfigured.
     */
    public function __construct($xmlPath, $developmentEnvironment, $includePath) {
        $xmlPath = $includePath."/".$xmlPath;
        if(!file_exists($xmlPath)) throw new Exception("XML configuration file not found!");
        $this->simpleXMLElement = simplexml_load_file($xmlPath);
        $this->includePath = $includePath;
        
        $this->setDefaultContentType();
        $this->setControllersPath();
        $this->setReportersPath();
        $this->setRenderersPath();
        $this->setViewsPath();
        $this->setPublicPath();
        $this->setDisplayErrors($developmentEnvironment);
        $this->setVersion();
        $this->setReporters($developmentEnvironment);
        $this->setRenderers();
        $this->setRoutes();
    }
    
    /**
     * Sets default response content type. Maps to tag application.default_default_content_type @ XML.
     */
    private function setDefaultContentType() {
        $this->defaultContentType = (string) $this->simpleXMLElement->application["default_content_type"];
        if(!$this->defaultContentType) throw new Exception("Attribute 'default_content_type' is mandatory for 'application' tag");
    }
    
    /**
     * Gets default response content type.
     *
     * @return string
     */
    public function getDefaultContentType() {
        return $this->defaultContentType;
    }
    
    /**
     * Sets path to controllers folder. Maps to tag application.paths.controllers @ XML.
     */
    private function setControllersPath() {
        $this->controllersPath = $this->includePath."/".$this->simpleXMLElement->application->paths->controllers;
    }
    
    /**
     * Gets path to controllers folder.
     *
     * @return string
     */
    public function getControllersPath() {
        return $this->controllersPath;
    }
    
    /**
     * Sets path to reporters folder. Maps to tag application.paths.reporters @ XML.
     */
    private function setReportersPath() {
        $this->reportersPath = $this->includePath."/".$this->simpleXMLElement->application->paths->reporters;
    }
    
    /**
     * Gets path to reporters folder.
     *
     * @return string
     */
    public function getReportersPath() {
        return $this->reportersPath;
    }
    
    /**
     * Sets path to renderers folder. Maps to tag application.paths.renderers @ XML.
     */
    private function setRenderersPath() {
        $this->renderersPath = $this->includePath."/".$this->simpleXMLElement->application->paths->renderers;
    }
    
    /**
     * Gets path to renderers folder.
     *
     * @return string
     */
    public function getRenderersPath() {
        return $this->renderersPath;
    }
    
    /**
     * Sets views folder. Maps to application.paths.views @ XML.
     */
    private function setViewsPath() {
        $this->viewsPath = $this->includePath."/".$this->simpleXMLElement->application->paths->views;
    }
    
    /**
     * Gets path to public files folder.
     *
     * @return string
     */
    public function getPublicPath() {
        return $this->publicPath;
    }
    
    /**
     * Sets public files folder. Maps to application.paths.public @ XML.
     */
    private function setPublicPath() {
        $this->publicPath = $this->includePath."/".$this->simpleXMLElement->application->paths->public;
    }
    
    /**
     * Gets path to views folder.
     *
     * @return string
     */
    public function getViewsPath() {
        return $this->viewsPath;
    }
    
    /**
     * Sets whether or not error details should be displayed. Maps to application.display_errors @ XML / environment
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     */
    private function setDisplayErrors($developmentEnvironment) {
        $value = $this->simpleXMLElement->application->display_errors->{$developmentEnvironment};
        $this->displayErrors = (string) $value?true:false;
    }
    
    /**
     * Gets whether or not error details should be displayed.
     *
     * @return boolean
     */
    public function getDisplayErrors() {
        return $this->displayErrors;
    }
    
    /**
     * Sets application version. Maps to application.paths.public @ XML.
     */
    private function setVersion() {
        $this->version = (string) $this->simpleXMLElement->application["version"];
    }
    
    /**
     * Gets application version.
     *
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }
    
    /**
     * Sets ErrorReporter instances that will later be used to report exception to. Maps to tag reporters @ XML.
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     * @throws Exception If XML is misconfigured.
     */
    private function setReporters($developmentEnvironment) {
        if($this->simpleXMLElement->reporters->{$developmentEnvironment}===null) return;
        $erp = new ErrorReportersFinder($this->simpleXMLElement->reporters->{$developmentEnvironment});
        $this->reporters = $erp->getReporters();
    }
    
    /**
     * Gets ErrorReporter instances that will later on be used to report exception to
     *
     * @param string $className
     * @return mixed
     */
    public function reporters($className="") {
        if(!$className) return $this->reporters;
        else return (isset($this->reporters[$className])?$this->reporters[$className]:null);
    }
    
    /**
     * Sets ErrorRenderer instances that will later be used to render response to exception. Maps to tag renderers @ XML.
     *
     * @throws Exception If XML is misconfigured.
     */
    private function setRenderers() {
        $erf = new ErrorRenderersFinder($this->simpleXMLElement->renderers);
        $this->renderers = $erf->getRenderers();
    }
    
    /**
     * Gets ErrorRenderer instances that will later be used to render response to exception
     *
     * @param string $contentType
     * @return mixed
     */
    public function renderers($contentType="") {
        if(!$contentType) return $this->renderers;
        else return (isset($this->renderers[$contentType])?$this->renderers[$contentType]:null);
    }
    
    /**
     * Sets routes that map exceptions that will later on be used to resolve controller & view. Maps to tag exceptions @ XML
     *
     * @throws Exception If XML is misconfigured.
     */
    private function setRoutes() {
        $rf = new RoutesFinder($this->simpleXMLElement->exceptions);
        $this->routes = $rf->getRoutes();
    }
    
    /**
     * Gets routes that map exceptions that will later on be used to resolve controller & view.
     *
     * @param string $exceptionClassName
     * @return mixed
     */
    public function routes($exceptionClassName="") {
        if(!$exceptionClassName) return $this->routes;
        else return (isset($this->routes[$exceptionClassName])?$this->routes[$exceptionClassName]:null);
    }
    
    /**
     * Gets tag based on name from main XML root or referenced XML file if "ref" attribute was set
     *
     * @param string $name
     * @return \SimpleXMLElement
     */
    public function getTag($name) {
        $xml = $this->simpleXMLElement->{$name};
        $xmlFilePath = (string) $xml["ref"];
        if($xmlFilePath) {
            $xmlFilePath = $this->includePath."/".$xmlFilePath.".xml";
            if(!file_exists($xmlFilePath)) throw new Exception("XML file not found: ".$xmlFilePath);
            $subXML = simplexml_load_file($xmlFilePath);
            return $subXML->{$name};
        } else {
            return $xml;
        }
    }
}