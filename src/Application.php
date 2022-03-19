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
    private bool $displayErrors=false;
    private array $reporters=array();
    private string $developmentEnvironment;
    
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
        $this->setDisplayErrors();
        $this->setReporters();
        $this->setRoutes();
        $this->setResolvers();
    }
    
    /**
     * Sets whether or not error details should be displayed based on contents of "display_errors" XML tag:
     */
    private function setDisplayErrors(): void
    {
        $xml = $this->getTag("display_errors");
        $value = $xml->{$this->developmentEnvironment};
        $this->displayErrors = (bool)((string)$value);
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
        $list = $xml->xpath("reporter");
        foreach ($list as $info) {
            $reporterClass = (string) $info['class'];
            if (!$reporterClass) {
                throw new ConfigurationException("Attribute 'class' is mandatory for 'reporter' tag");
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
        $list = $xml->xpath("route");
        foreach ($list as $info) {
            $id = (string) $info['id'];
            if (!$id) {
                throw new ConfigurationException("Attribute 'id' is mandatory for 'route' tag");
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
    public function reporters(string $className=""): \SimpleXMLElement|array|null
    {
        if (!$className) {
            return $this->reporters;
        } else {
            return ($this->reporters[$className] ?? null);
        }
    }
}
