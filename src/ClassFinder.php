<?php
namespace Lucinda\MVC\STDERR;

/**
 * Locates and validates class on disk
 */
class ClassFinder
{
    private $className;

    /**
     * Performs detection process
     *
     * @param string $classPath Relative location of folder class lies into.
     * @param string $className Name of class (including namespace, if applies)
     * @throws Exception If validation fails (file or class not found)
     */
    public function __construct($classPath, $className) {
        $this->setName($classPath, $className);
    }

    /**
     * Performs detection process
     *
     * @param string $classPath Relative location of folder class lies into.
     * @param string $className Name of class (including namespace, if applies)
     * @throws Exception If validation fails (file or class not found)
     */
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

    /**
     * Gets valid class name (incl. namespace)
     *
     * @return string
     */
    public function getName() {
        return $this->className;
    }
}