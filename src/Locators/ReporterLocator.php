<?php
namespace Lucinda\STDERR\Locators;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\Exception;

/**
 * Locates reporters on disk based on reporters path & <reporter> tags detected beforehand
 */
class ReporterLocator extends ServiceLocator
{
    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param string $className Name of class in XML
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application, string $className)
    {
        $this->setClassName($application, $className);
    }

    /**
     * Finds reporter on disk
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param string $className Name of class in XML
     * @throws Exception If detection fails due to file/class not found.
     */
    private function setClassName(Application $application, string $className): void
    {
        $classFinder = new ClassFinder($application->getReportersPath());
        $this->className = $classFinder->find($className);
    }
}
