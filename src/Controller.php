<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates an abstract controller that feeds on exceptions, able to set exception-specific rendering/reporting policies
 */
abstract class Controller
{
    protected $application, $request, $response;

    /**
     * Controller constructor.
     * @param Application $application
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Application $application, Request $request, Response $response) {
        $this->application = $application;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Executes controller logic, which interfaces received exception with models (eg: reporters) and response
     * 
     * @return ErrorReporter[]
     */
    abstract public function run();
}