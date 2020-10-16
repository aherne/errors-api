<?php
namespace Lucinda\MVC\STDERR;

/**
 * Defines blueprint for rendering a response back to caller after an exception fed STDERR
 */
abstract class ErrorRenderer
{
    protected $application;
    
    /**
     * Saves Application objects to be available in implemented renderers
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
    
    /**
     * Renders response to screen.
     *
     * @param Response $response Encapsulates response to send back to caller.
     */
    abstract public function render(Response $response);
}
