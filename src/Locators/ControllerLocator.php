<?php
namespace Lucinda\STDERR\Locators;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\Request;
use Lucinda\STDERR\Exception;

/**
 * Locates MVC controller on disk based on controller path & route detected beforehand
 */
class ControllerLocator extends ServiceLocator
{
    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application, Request $request): void
    {
        if (!$request->getRoute()->getController()) {
            return;
        }
        $this->setClassName($application, $request);
    }

    /**
     * Finds controller on disk
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @throws Exception If detection fails due to file/class not found or not instanceof \\Lucinda\\STDERR\\Controller.
     */
    private function setClassName(Application $application, Request $request): void
    {
        $classFinder = new ClassFinder($application->getControllersPath());
        $this->className = $classFinder->find($request->getRoute()->getController());
    }
}
