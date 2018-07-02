<?php
namespace Lucinda\Framework\STDERR;

require_once("Controller.php");

class ControllerFinder {
    private $controller;
    
    public function __construct(Application $application, Route $route, View $view, $reporters) {
        $this->setController($application, $route, $view, $reporters);
    }
    
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
     * @return Controller
     */
    public function getController() {
        return $this->controller;
    }
}

