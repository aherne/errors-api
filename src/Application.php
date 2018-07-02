<?php
namespace Lucinda\Framework\STDERR;

/**
 * Detects MVC Errors API settings from XML
 */
class Application
{
    private $simpleXMLElement;
    private $controllersPath, $viewsPath, $reportersPath, $renderersPath, $defaultContentType, $displayErrors = false;
    
    public function __construct($xmlPath, $developmentEnvironment) {
        if(!file_exists($xmlPath)) throw new Exception("XML configuration file not found!");
        $this->simpleXMLElement = simplexml_load_file($xmlPath);

        $this->setDisplayErrors($developmentEnvironment);
        $this->setDefaultContentType();
        $this->setControllersPath();
        $this->setViewsPath();
        $this->setReportersPath();
        $this->setRenderersPath();
        $this->setViewsPath();
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
     * Sets reporters folder. Maps to application.paths.reporters @ XML.
     */
    private function setReportersPath() {
        $this->reportersPath = (string) $this->simpleXMLElement->application->paths->reporters;
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
     * Sets renderers folder. Maps to application.paths.renderers @ XML.
     */
    private function setRenderersPath() {
        $this->renderersPath = (string) $this->simpleXMLElement->application->paths->renderers;
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
}

