<?php
namespace Lucinda\MVC\STDERR;

class ClassFinder
{
    private $className;

    public function __construct($classPath, $className) {
        $this->setName($classPath, $className);
    }

    private function setName($classPath, $className) {
        // get actual class name without namespace
        $slashPosition = strpos($className, "\\");
        $simpleClassName = ($slashPosition!==false?substr($className,$slashPosition+1):$className);

        // loads class file
        $filePath = $classPath."/".$simpleClassName.".php";
        if(!file_exists($filePath)) throw new Exception("File not found: ".$simpleClassName);
        require_once($filePath);

        // validates if class exists
        if(!class_exists($className)) throw new Exception("Class not found: ".$className);

        // saves found class
        $this->className = $className;
    }

    public function getName() {
        return $this->className;
    }
}