<?php
namespace Lucinda\Framework\STDERR;

require_once("ErrorReportersFinder.php");
require_once("ErrorRenderersFinder.php");
require_once("RoutesFinder.php");
require_once("ReportersList.php");

/**
 * Detects MVC Errors API settings from XML
 */
class Application
{
    private $simpleXMLElement;
    private $controllersPath, $viewsPath, $defaultContentType, $displayErrors = false;
    private $reporters=array(), $renderers=array(), $routes=array();
    
    public function __construct($xmlPath, $developmentEnvironment) {
        if(!file_exists($xmlPath)) throw new Exception("XML configuration file not found!");
        $this->simpleXMLElement = simplexml_load_file($xmlPath);

        $this->setDisplayErrors($developmentEnvironment);
        $this->setDefaultContentType();
        $this->setControllersPath();
        $this->setViewsPath();
        $this->setReporters($developmentEnvironment);
        $this->setRenderers();
        $this->setRoutes();
    }
    
    /**
     * Sets default response content type. Maps to application.default_default_content_type @ XML.
     */
    private function setDefaultContentType() {
        $this->defaultContentType = (string) $this->simpleXMLElement->application->default_content_type;
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
     * Sets path to controllers folder. Maps to application.paths.controllers @ XML.
     */
    private function setControllersPath() {
        $this->controllersPath = (string) $this->simpleXMLElement->application->paths->controllers;
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
     * Sets views folder. Maps to application.paths.views @ XML.
     */
    private function setViewsPath() {
        $this->viewsPath = (string) $this->simpleXMLElement->application->paths->views;
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
     * Sets whether or not error details should be displayed in rendered response. Maps to application.display_errors @ XML.
     * 
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     */
    private function setDisplayErrors($developmentEnvironment) {
        $this->displayErrors = ((string) $this->simpleXMLElement->application->display_errors->{$developmentEnvironment} ? true : false);
    }
    
    /**
     * Gets whether or not error details should be displayed in rendered response
     * 
     * @return boolean
     */
    public function getDisplayErrors() {
        return $this->displayErrors;
    }
    
    /**
     * Gets a pointer to XML file reader.
     *
     * @return \SimpleXMLElement
     */
    public function getXML() {
        return $this->simpleXMLElement;
    }
    
    private function setReporters($developmentEnvironment) {
        $erp = new ErrorReportersFinder(
            $this->simpleXMLElement->reporters->{$developmentEnvironment},
            (string) $this->simpleXMLElement->application->paths->reporters
            );
        $this->reporters = new ReportersList($erp->getReporters());
    }
    
    public function getReporters() {
        return $this->reporters;
    }
    
    private function setRenderers() {
        $erf = new ErrorRenderersFinder(
            $this->simpleXMLElement->renderers,
            (string) $this->simpleXMLElement->application->paths->renderers
            );
        $this->renderers = $erf->getRenderers();
    }
    
    public function getRenderers() {
        return $this->renderers;
    }
    
    private function setRoutes() {
        $rf = new RoutesFinder($this->simpleXMLElement->exceptions);
        $this->routes = $rf->getRoutes();
    }
    
    public function getRoutes() {
        return $this->routes;
    }
}

