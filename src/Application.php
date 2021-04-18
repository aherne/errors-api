<?php
namespace Lucinda\STDERR;

use Lucinda\MVC\ConfigurationException;
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
class Application extends \Lucinda\MVC\Application
{
    private $reportersPath;
    private $displayErrors=false;
    private $reporters=array();
    private $developmentEnvironment;
    
    /**
     * Performs detection process.
     *
     * @param string $xmlFilePath Relative location of XML file containing settings.
     * @param string $developmentEnvironment Development environment server is running into (eg: local, dev, live)
     * @throws ConfigurationException If XML is misconfigured.
     */
    public function __construct(string $xmlFilePath, string $developmentEnvironment)
    {
        $this->developmentEnvironment = $developmentEnvironment;
        $this->readXML($xmlFilePath);
        $this->setApplicationInfo();
        $this->setReporters();
        $this->setRoutes();
        $this->setResolvers();
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Application::setApplicationInfo()
     */
    protected function setApplicationInfo(): void
    {
        parent::setApplicationInfo();
        
        $xml = $this->getTag("application");
        
        $this->reportersPath = (string) $xml->paths["reporters"];
        
        $value = $xml->display_errors->{$this->developmentEnvironment};
        $this->displayErrors = (string) $value?true:false;
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
     * Gets whether or not error details should be displayed.
     *
     * @return boolean
     */
    public function getDisplayErrors(): bool
    {
        return $this->displayErrors;
    }
    
    /**
     * Reads content of tag reporters
     *
     * @throws ConfigurationException If XML is misconfigured.
     */
    private function setReporters(): void
    {
        $xml = $this->getTag("reporters")->{$this->developmentEnvironment};
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
     * {@inheritDoc}
     * @see \Lucinda\MVC\Application::setRoutes()
     */
    protected function setRoutes(): void
    {
        $xml = $this->getTag("routes");
        $list = $xml->xpath("//route");
        foreach ($list as $info) {
            $id = (string) $info['id'];
            if (!$id) {
                throw new ConfigurationException("Route missing 'id' attribute!");
            }
            $this->routes[$id] = new Route($info);
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
}
