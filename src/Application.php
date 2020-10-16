<?php
namespace Lucinda\MVC\STDERR;

require("application/ErrorReportersFinder.php");
require("application/ErrorRenderersFinder.php");
require("application/RoutesFinder.php");

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
    private $simpleXMLElement;
    private $controllersPath;
    private $viewsPath;
    private $reportersPath;
    private $renderersPath;
    private $publicPath;
    private $defaultFormat;
    private $version;
    private $reporters=array();
    private $renderers=array();
    private $routes=array();
    private $displayErrors=false;
    
    private $objectsCache=array();
    
    /**
     * Performs detection process.
     *
     * @param string $xmlPath Relative location of XML file containing settings.
     * @param string $developmentEnvironment Development environment server is running into (eg: local, dev, live)
     * @throws Exception If XML is misconfigured.
     */
    public function __construct($xmlPath, $developmentEnvironment)
    {
        if (!file_exists($xmlPath)) {
            throw new Exception("XML configuration file not found!");
        }
        $this->simpleXMLElement = \simplexml_load_file($xmlPath);
        
        $xml = $this->getTag("application");
        
        $this->setDefaultFormat($xml);
        $this->setControllersPath($xml);
        $this->setReportersPath($xml);
        $this->setRenderersPath($xml);
        $this->setViewsPath($xml);
        $this->setPublicPath($xml);
        $this->setDisplayErrors($xml, $developmentEnvironment);
        $this->setVersion($xml);
        $this->setReporters($developmentEnvironment);
        $this->setRenderers();
        $this->setRoutes();
    }
    
    /**
     * Gets default response display format (eg: html or json)
     */
    private function setDefaultFormat(\SimpleXMLElement $xml)
    {
        $this->defaultFormat = (string) $xml["default_format"];
        if (!$this->defaultFormat) {
            throw new Exception("Attribute 'default_format' is mandatory for 'application' tag");
        }
    }
    
    /**
     * Gets default response display format
     *
     * @return string
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }
    
    /**
     * Sets path to controllers folder. Maps to tag application.paths.controllers @ XML.
     */
    private function setControllersPath(\SimpleXMLElement $xml)
    {
        $this->controllersPath = $xml->paths->controllers;
    }
    
    /**
     * Gets path to controllers folder.
     *
     * @return string
     */
    public function getControllersPath()
    {
        return $this->controllersPath;
    }
    
    /**
     * Sets path to reporters folder. Maps to tag application.paths.reporters @ XML.
     */
    private function setReportersPath(\SimpleXMLElement $xml)
    {
        $this->reportersPath = $xml->paths->reporters;
    }
    
    /**
     * Gets path to reporters folder.
     *
     * @return string
     */
    public function getReportersPath()
    {
        return $this->reportersPath;
    }
    
    /**
     * Sets path to renderers folder. Maps to tag application.paths.renderers @ XML.
     */
    private function setRenderersPath(\SimpleXMLElement $xml)
    {
        $this->renderersPath = $xml->paths->renderers;
    }
    
    /**
     * Gets path to renderers folder.
     *
     * @return string
     */
    public function getRenderersPath()
    {
        return $this->renderersPath;
    }
    
    /**
     * Sets views folder. Maps to application.paths.views @ XML.
     */
    private function setViewsPath(\SimpleXMLElement $xml)
    {
        $this->viewsPath = $xml->paths->views;
    }
    
    /**
     * Gets path to public files folder.
     *
     * @return string
     */
    public function getPublicPath()
    {
        return $this->publicPath;
    }
    
    /**
     * Sets public files folder. Maps to application.paths.public @ XML.
     */
    private function setPublicPath(\SimpleXMLElement $xml)
    {
        $this->publicPath = $xml->paths->public;
    }
    
    /**
     * Gets path to views folder.
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->viewsPath;
    }
    
    /**
     * Sets whether or not error details should be displayed. Maps to application.display_errors @ XML / environment
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     */
    private function setDisplayErrors(\SimpleXMLElement $xml, $developmentEnvironment)
    {
        $value = $xml->display_errors->{$developmentEnvironment};
        $this->displayErrors = (string) $value?true:false;
    }
    
    /**
     * Gets whether or not error details should be displayed.
     *
     * @return boolean
     */
    public function getDisplayErrors()
    {
        return $this->displayErrors;
    }
    
    /**
     * Sets application version. Maps to application.paths.public @ XML.
     */
    private function setVersion(\SimpleXMLElement $xml)
    {
        $this->version = (string) $xml["version"];
    }
    
    /**
     * Gets application version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Sets ErrorReporter instances that will later be used to report exception to. Maps to tag reporters @ XML.
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     * @throws Exception If XML is misconfigured.
     */
    private function setReporters($developmentEnvironment)
    {
        $xml = $this->getTag("reporters");
        if ($xml->{$developmentEnvironment}===null) {
            return;
        }
        $erp = new ErrorReportersFinder($xml->{$developmentEnvironment});
        $this->reporters = $erp->getReporters();
    }
    
    /**
     * Gets ErrorReporter instances that will later on be used to report exception to
     *
     * @param string $className
     * @return \SimpleXMLElement[string]|NULL|\SimpleXMLElement
     */
    public function reporters($className="")
    {
        if (!$className) {
            return $this->reporters;
        } else {
            return (isset($this->reporters[$className])?$this->reporters[$className]:null);
        }
    }
    
    /**
     * Sets ErrorRenderer instances that will later be used to render response to exception. Maps to tag renderers @ XML.
     *
     * @throws Exception If XML is misconfigured.
     */
    private function setRenderers()
    {
        $erf = new ErrorRenderersFinder($this->getTag("renderers"));
        $this->renderers = $erf->getRenderers();
    }
    
    /**
     * Gets ErrorRenderer instances that will later be used to render response to exception
     *
     * @param string $displayFormat
     * @return Format[string]|NULL|Format
     */
    public function renderers($displayFormat="")
    {
        if (!$displayFormat) {
            return $this->renderers;
        } else {
            return (isset($this->renderers[$displayFormat])?$this->renderers[$displayFormat]:null);
        }
    }
    
    /**
     * Sets routes that map exceptions that will later on be used to resolve controller & view. Maps to tag exceptions @ XML
     *
     * @throws Exception If XML is misconfigured.
     */
    private function setRoutes()
    {
        $rf = new RoutesFinder($this->getTag("exceptions"));
        $this->routes = $rf->getRoutes();
    }
    
    /**
     * Gets routes that map exceptions that will later on be used to resolve controller & view.
     *
     * @param string $exceptionClassName
     * @return Route[string]|NULL|Route
     */
    public function routes($exceptionClassName="")
    {
        if (!$exceptionClassName) {
            return $this->routes;
        } else {
            return (isset($this->routes[$exceptionClassName])?$this->routes[$exceptionClassName]:null);
        }
    }
    
    /**
     * Gets tag based on name from main XML root or referenced XML file if "ref" attribute was set
     *
     * @param string $name
     * @return \SimpleXMLElement
     */
    public function getTag($name)
    {
        $xml = $this->simpleXMLElement->{$name};
        $xmlFilePath = (string) $xml["ref"];
        if ($xmlFilePath) {
            if (isset($this->objectsCache[$name])) {
                return $this->objectsCache[$name];
            } else {
                $xmlFilePath = $xmlFilePath.".xml";
                if (!file_exists($xmlFilePath)) {
                    throw new Exception("XML file not found: ".$xmlFilePath);
                }
                $subXML = simplexml_load_file($xmlFilePath);
                $returningXML = $subXML->{$name};
                $this->objectsCache[$name] = $returningXML;
                return $returningXML;
            }
        } else {
            return $xml;
        }
    }
}
