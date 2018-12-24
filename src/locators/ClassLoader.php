<?php
namespace Lucinda\MVC\STDERR;

/**
 * Loads class file from disk and validates that class exists there 
 *
 * @param string $classPath Relative location of folder class lies into.
 * @param string $className Name of class (including namespace, if applies)
 * @throws Exception If validation fails (file or class not found)
 */
function load_class($classPath, $className) {
    // get actual class name without namespace
    $slashPosition = strpos($className, "\\");
    $simpleClassName = ($slashPosition!==false?substr($className,$slashPosition+1):$className);

    // loads class file
    $filePath = $classPath."/".$simpleClassName.".php";
    if(!file_exists($filePath)) throw new Exception("File not found: ".$simpleClassName);
    require_once($filePath);

    // validates if class exists
    if(!class_exists($className)) throw new Exception("Class not found: ".$className);
}