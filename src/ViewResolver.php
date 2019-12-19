<?php
namespace Lucinda\STDERR;


/**
 * Resolves View into a Response based on Application settings
 */
abstract class ViewResolver implements Runnable
{
    /**
     * @var Application
     */
    protected $application;
    
    /**
     * @var Response
     */
    protected $response;
    
    /**
     * Saves objects to be available in implemented getContent() methods.
     *
     * @param Application $application
     * @param Response $response
     */
    public function __construct(Application $application, Response $response)
    {
        $this->application = $application;
        $this->response = $response;
    }
    
    /**
     * Feeds response body with content generated from view by resolver
     */
    public function run(): void
    {
        $this->response->setBody($this->getContent());
    }
    
    /**
     * Gets body to feed response with
     *
     * @return string Content to display: string for html/json/xml response formats.
     */
    abstract protected function getContent(): string;
}
