<?php
namespace Lucinda\MVC\STDERR;

/**
 * Blueprint for locating a service class name on disk based on given criteria
 */
abstract class ServiceLocator
{
    protected $className;
    
    /**
     * Gets service class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
