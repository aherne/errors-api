<?php
namespace Lucinda\MVC\STDERR;

require_once("ClassLoader.php");

/**
 * Locates MVC controller on disk based on controller path & route detected beforehand,
 * then instances it from received parameters
 */
class ControllerLocator
{
    private $controller;

    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param Response $response Encapsulates response to send back to caller.
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application, Request $request, Response $response)
    {
        $this->setController($application, $request, $response);
    }

    /**
     * Finds controller on disk, instances it with received parameters and saves result
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param Response $response Encapsulates response to send back to caller.
     * @throws Exception If detection fails due to file/class not found.
     */
    private function setController(Application $application, Request $request, Response $response)
    {
        $controllerClass = $request->getRoute()->getController();
        load_class($application->getControllersPath(), $controllerClass);
        $object = new $controllerClass($application, $request, $response);
        if (!($object instanceof Controller)) {
            throw new Exception("Class must be instance of Controller");
        }
        $this->controller = $object;
    }

    /**
     * Gets controller found.
     *
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }
}
