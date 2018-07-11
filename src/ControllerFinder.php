<?php
namespace Lucinda\MVC\STDERR;

require_once("Controller.php");
require_once("ClassFinder.php");

/**
 * Locates controller on disk based on XML, then instances it via received parameters
 */
class ControllerFinder {
    private $controller;

    /**
     * ControllerFinder constructor.
     *
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @throws Exception
     */
    public function __construct(Application $application, Request $request, Response $response) {
        $this->setController($application, $request, $response);
    }

    /**
     * Finds controller on disk and saves result
     *
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @param ErrorReporter[] $reporters
     * @throws Exception
     */
    private function setController(Application $application, Request $request, Response $response) {
        $classFinder = new ClassFinder($application->getControllersPath(), $request->getRoute()->getController());
        $controllerClass = $classFinder->getName();
        $object = new $controllerClass($application, $request, $response);
        if(!($object instanceof Controller)) throw new Exception("Class must be instance of Controller");
        $this->controller = $object;
    }

    /**
     * Gets controller found.
     *
     * @return Controller
     */
    public function getController() {
        return $this->controller;
    }
}

