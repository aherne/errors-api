<?php
namespace Lucinda\MVC\STDERR;

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
    private $viewResolversPath;
    private $publicPath;
    private $defaultFormat;
    private $version;
    private $displayErrors=false;
    private $reporters=array();
    private $routes=array();
    private $formats=array();
    
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
        $this->simpleXMLElement = simplexml_load_file($xmlPath);
        
        $this->setApplicationInfo($developmentEnvironment);
        $this->setReporters($developmentEnvironment);
        $this->setRoutes();
        $this->setFormats();
    }
    
    /**
     * Sets basic application info based on contents of "application" XML tag
     * 
     * @param string $developmentEnvironment
     * @throws Exception If xml content has failed validation.
     */
    private function setApplicationInfo($developmentEnvironment): void
    {
        $xml = $this->getTag("application");
        if (empty($xml)) {
            throw new Exception("Tag is mandatory: application");
        }
        
        $this->defaultFormat = (string) $xml["default_format"];
        if (!$this->defaultFormat) {
            throw new Exception("Attribute 'default_format' is mandatory for 'application' tag");
        }
        
        $this->controllersPath = (string) $xml->paths->controllers;
        $this->reportersPath = (string) $xml->paths->reporters;
        $this->viewResolversPath = (string) $xml->paths->resolvers;
        $this->viewsPath = (string) $xml->paths->views;
        $this->publicPath = (string) $xml->paths->public;
        $this->version = (string) $xml["version"];
        
        $value = $this->simpleXMLElement->application->display_errors->{$developmentEnvironment};
        $this->displayErrors = (string) $value?true:false;
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
     * Gets path to controllers folder.
     *
     * @return string
     */
    public function getControllersPath()
    {
        return $this->controllersPath;
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
     * Gets path to view resolvers folder.
     *
     * @return string
     */
    public function getViewResolversPath()
    {
        return $this->viewResolversPath;
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
     * Gets path to views folder.
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->viewsPath;
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
     * Gets application version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Reads content of tag reporters
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     * @throws Exception If XML is misconfigured.
     */
    private function setReporters($developmentEnvironment)
    {
        $xml = $this->simpleXMLElement->reporters->{$developmentEnvironment};
        if ($xml===null) {
            return;
        }
        $tmp = (array) $xml;
        if (empty($tmp["reporter"])) {
            return;
        }
        $list = (is_array($tmp["reporter"])?$tmp["reporter"]:[$tmp["reporter"]]);
        foreach ($list as $info) {
            $reporterClass = (string) $info['class'];
            if (!$reporterClass) {
                throw new Exception("Reporter tag missing class attribute");
            }
            $this->reporters[$reporterClass] = $info;
        }        
    }
    
    /**
     * Gets content of tag reporters
     *
     * @param string $className
     * @return \SimpleXMLElement|array|null
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
     * Reads content of tag formats
     *
     * @throws Exception If XML is misconfigured.
     */
    private function setFormats()
    {
        $xml = $this->simpleXMLElement->formats;
        $tmp = (array) $xml;
        if (empty($tmp["format"])) {
            return;
        }
        $list = (is_array($tmp["format"])?$tmp["format"]:[$tmp["format"]]);
        foreach ($list as $info) {
            $name = (string) $info["name"];
            if (!$name) {
                throw new Exception("Format missing name!");
            }
            $this->formats[$name] = new Format($info);
        }
    }
    
    /**
     * Gets content of tag formats encapsulated as Format objects
     *
     * @param string $displayFormat
     * @return Format|array|null
     */
    public function formats($displayFormat="")
    {
        if (!$displayFormat) {
            return $this->formats;
        } else {
            return (isset($this->formats[$displayFormat])?$this->formats[$displayFormat]:null);
        }
    }
    
    /**
     * Reads content of tag exceptions 
     *
     * @throws Exception If XML is misconfigured.
     */
    private function setRoutes()
    {
        $xml = $this->simpleXMLElement->exceptions;
        
        // get default route
        $this->routes[""] = $this->compileRoute($xml);
        
        // override with specific route, if set
        $tmp = (array) $xml;
        if (empty($tmp["exception"])) {
            return [];
        }
        $list = (is_array($tmp["exception"])?$tmp["exception"]:[$tmp["exception"]]);
        foreach ($list as $info) {
            $currentClassName = (string) $info['class'];
            if (!$currentClassName) {
                throw new Exception("Exception class not defined!");
            }
            $this->routes[$currentClassName] = new Route($info);
        }
    }
    
    /**
     * Reads content of tag exceptions encapsulated as Route objects
     *
     * @param string $exceptionClassName
     * @return Route|array|null
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
            $xmlFilePath = $xmlFilePath.".xml";
            if (!file_exists($xmlFilePath)) {
                throw new Exception("XML file not found: ".$xmlFilePath);
            }
            $subXML = simplexml_load_file($xmlFilePath);
            return $subXML->{$name};
        } else {
            return $xml;
        }
    }
}
