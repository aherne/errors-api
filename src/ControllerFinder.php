<?php
namespace Lucinda\Framework\STDERR;

require_once("Controller.php");

/**
 * Locates controller on disk based on XML, then instances it via received parameters
 */
class ControllerFinder {
    private $controller;

    /**
     * ControllerFinder constructor.
     *
     * @param Application $application
     * @param Route $route
     * @param View $view
     * @param ErrorReporter[] $reporters
     * @throws Exception
     */
    public function __construct(Application $application, Route $route, View $view, $reporters) {
        $this->setController($application, $route, $view, $reporters);
    }

    /**
     * Finds controller on disk and saves result
     *
     * @param Application $application
     * @param Route $route
     * @param View $view
     * @param ErrorReporter[] $reporters
     * @throws Exception
     */
    private function setController(Application $application, Route $route, View $view, $reporters) {
        $controllerPath = $application->getControllersPath()."/".$route->getController().".php";
        if(!file_exists($controllerPath)) throw new Exception("Controller file not found: ".$controllerPath);
        require_once($controllerPath);

        $controllerClass = $route->getController();
        if(!class_exists($controllerClass)) throw new Exception("Controller class not found: ".$controllerClass);

        $object = new $controllerClass($application, $route, $view, $reporters);
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

