<?php
namespace Lucinda\STDERR\Locators;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\Application\Format;
use Lucinda\STDERR\Exception;

/**
 * Locates view resolver class on disk based on path & formats tag
 */
class ViewResolverLocator extends ServiceLocator 
{
    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Format $detectedResponseFormat Response format detected by FrontController
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application, Format $detectedResponseFormat): void
    {
        $this->setClassName($application, $detectedResponseFormat);
    }

    /**
     * Finds view resolver class on disk based on path & formats tag
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Format $detectedResponseFormat Response format detected by FrontController
     * @throws Exception If detection fails due to file/class not found.
     */
    private function setClassName(Application $application, Format $detectedResponseFormat): void
    {
        $classFinder = new ClassFinder($application->getViewResolversPath());
        $this->className = $classFinder->find($detectedResponseFormat->getViewResolver());
    }
}
