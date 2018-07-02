<?php
namespace Lucinda\Framework\STDERR;

/**
 * Encapsulates an abstract controller that feeds on exceptions, able to set exception-specific rendering/reporting policies
 */
abstract class Controller
{
    protected $application, $route, $view, $reporters;

    /**
     * Controller constructor.
     * @param Application $application
     * @param Route $route
     * @param View $view
     * @param ErrorReporter[] $reporters
     */
    public function __construct(Application $application, Route $route, View $view, $reporters) {
        $this->application = $application;
        $this->route = $route;
        $this->view = $view;
        $this->reporters = $reporters;
    }

    /**
     * Executes controller logic, which interfaces received exception with models (eg: reporters) and view
     *
     * @param Exception|Error $exception Exception routed.
     * @return ErrorReporter[] List of reporters to send exception to.
     */
    abstract public function run($exception);
}