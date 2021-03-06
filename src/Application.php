<?php
namespace Lucinda\STDERR;

use Lucinda\STDERR\Application\Format;
use Lucinda\STDERR\Application\Route;

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
    private $defaultFormat;
    private $version;
    private $displayErrors=false;
    private $reporters=array();
    private $routes=array();
    private $resolvers=array();
    
    private $objectsCache=array();    
    
    /**
     * Performs detection process.
     *
     * @param string $xmlFilePath Relative location of XML file containing settings.
     * @param string $developmentEnvironment Development environment server is running into (eg: local, dev, live)
     * @throws ConfigurationException If XML is misconfigured.
     */
    public function __construct(string $xmlFilePath, string $developmentEnvironment)
    {
        if (!file_exists($xmlFilePath)) {
            throw new ConfigurationException("XML file not found: ".$xmlFilePath);
        }
        $this->simpleXMLElement = simplexml_load_file($xmlFilePath);
        
        $this->setApplicationInfo($developmentEnvironment);
        $this->setReporters($developmentEnvironment);
        $this->setRoutes();
        $this->setResolvers();
    }
    
    /**
     * Sets basic application info based on contents of "application" XML tag
     *
     * @param string $developmentEnvironment
     * @throws ConfigurationException If xml content has failed validation.
     */
    private function setApplicationInfo(string $developmentEnvironment): void
    {
        $xml = $this->getTag("application");
        if (empty($xml)) {
            throw new ConfigurationException("Tag is mandatory: application");
        }
        
        $this->defaultFormat = (string) $xml["default_format"];
        if (!$this->defaultFormat) {
            throw new ConfigurationException("Attribute 'default_format' is mandatory for 'application' tag");
        }
        
        $this->controllersPath = (string) $xml->paths["controllers"];
        $this->reportersPath = (string) $xml->paths["reporters"];
        $this->viewResolversPath = (string) $xml->paths["resolvers"];
        $this->viewsPath = (string) $xml->paths["views"];
        $this->version = (string) $xml["version"];
        
        $value = $xml->display_errors->{$developmentEnvironment};
        $this->displayErrors = (string) $value?true:false;
    }
    
    /**
     * Gets default response display format
     *
     * @return string
     */
    public function getDefaultFormat(): string
    {
        return $this->defaultFormat;
    }
    
    /**
     * Gets path to controllers folder.
     *
     * @return string
     */
    public function getControllersPath(): string
    {
        return $this->controllersPath;
    }
    
    /**
     * Gets path to reporters folder.
     *
     * @return string
     */
    public function getReportersPath(): string
    {
        return $this->reportersPath;
    }
    
    /**
     * Gets path to view resolvers folder.
     *
     * @return string
     */
    public function getViewResolversPath(): string
    {
        return $this->viewResolversPath;
    }
    
    /**
     * Gets path to views folder.
     *
     * @return string
     */
    public function getViewsPath(): string
    {
        return $this->viewsPath;
    }
    
    /**
     * Gets whether or not error details should be displayed.
     *
     * @return boolean
     */
    public function getDisplayErrors(): bool
    {
        return $this->displayErrors;
    }
    
    /**
     * Gets application version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
    
    /**
     * Reads content of tag reporters
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     * @throws ConfigurationException If XML is misconfigured.
     */
    private function setReporters(string $developmentEnvironment): void
    {
        $xml = $this->getTag("reporters")->{$developmentEnvironment};
        if ($xml===null) {
            return;
        }
        $list = $xml->xpath("//reporter");
        foreach ($list as $info) {
            $reporterClass = (string) $info['class'];
            if (!$reporterClass) {
                throw new ConfigurationException("Reporter tag missing 'class' attribute");
            }
            $this->reporters[$reporterClass] = $info;
        }
        if (empty($this->reporters)) {
            throw new ConfigurationException("Tag is empty: reporters");
        }
    }
    
    /**
     * Gets content of tag reporters
     *
     * @param string $className
     * @return \SimpleXMLElement|array|null
     */
    public function reporters(string $className="")
    {
        if (!$className) {
            return $this->reporters;
        } else {
            return (isset($this->reporters[$className])?$this->reporters[$className]:null);
        }
    }
    
    
    /**
     * Reads content of tag resolvers
     *
     * @throws ConfigurationException If XML is misconfigured.
     */
    private function setResolvers(): void
    {
        $xml = $this->getTag("resolvers");
        if ($xml===null) {
            throw new ConfigurationException("Tag is required: resolvers");
        }
        $list = $xml->xpath("//resolver");
        foreach ($list as $info) {
            $name = (string) $info["format"];
            if (!$name) {
                throw new ConfigurationException("Resolver missing 'format' attribute!");
            }
            $this->resolvers[$name] = new Format($info);
        }
        if (empty($this->resolvers)) {
            throw new ConfigurationException("Tag is empty: resolvers");
        }
    }
    
    /**
     * Gets content of tag resolvers encapsulated as Format objects
     *
     * @param string $displayFormat
     * @return Format|array|null
     */
    public function resolvers(string $displayFormat="")
    {
        if (!$displayFormat) {
            return $this->resolvers;
        } else {
            return (isset($this->resolvers[$displayFormat])?$this->resolvers[$displayFormat]:null);
        }
    }
    
    /**
     * Reads content of tag exceptions
     *
     * @throws ConfigurationException If XML is misconfigured.
     */
    private function setRoutes(): void
    {
        $xml = $this->getTag("exceptions");
        
        // get default route
        $this->routes[""] = new Route($xml);
        
        // override with specific route, if set
        $list = $xml->xpath("//exception");
        foreach ($list as $info) {
            $currentClassName = (string) $info['class'];
            if (!$currentClassName) {
                throw new ConfigurationException("Exception class not defined!");
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
    public function routes(string $exceptionClassName="")
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
     * @throws ConfigurationException If XML is misconfigured.
     */
    public function getTag(string $name): \SimpleXMLElement
    {
        $xml = $this->simpleXMLElement->{$name};
        $xmlFilePath = (string) $xml["ref"];
        if ($xmlFilePath) {
            if (isset($this->objectsCache[$name])) {
                return $this->objectsCache[$name];
            } else {
                $xmlFilePath = $xmlFilePath.".xml";
                if (!file_exists($xmlFilePath)) {
                    throw new ConfigurationException("XML file not found: ".$xmlFilePath);
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

    /**
     * Gets root XML tag
     *
     * @return \SimpleXMLElement
     */
    public function getXML(): \SimpleXMLElement
    {
        return $this->simpleXMLElement;
    }
}
