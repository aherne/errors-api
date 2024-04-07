<?php

namespace Lucinda\STDERR;

use Lucinda\MVC\Application\Format;
use Lucinda\MVC\Response;
use Lucinda\STDERR\Application\Route;
use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;

/**
 * Error handler that bootstraps all uncaught exceptions and PHP errors as a STDERR front controller that feeds on
 * exception instances instead of requests.
 */
class FrontController implements ErrorHandler
{
    protected ?string $displayFormat = null;
    protected string $documentDescriptor;
    protected string $developmentEnvironment;
    protected string $includePath;
    protected ErrorHandler $emergencyHandler;

    /**
     * Redirects all uncaught exceptions and PHP errors in current application to itself.
     *
     * @param string       $documentDescriptor     Path to XML file containing your application settings.
     * @param string       $developmentEnvironment Development environment application is running into (eg: local, dev, live)
     * @param string       $includePath            Absolute root path where reporters / resolvers / controllers / views should be located
     * @param ErrorHandler $emergencyHandler       Handler to use if an error occurs while FrontController handles an exception
     */
    public function __construct(
        string $documentDescriptor,
        string $developmentEnvironment,
        string $includePath,
        ErrorHandler $emergencyHandler
    ) {
        // sets up system to track errors
        error_reporting(E_ALL);
        set_error_handler('\\Lucinda\\STDERR\\PHPException::nonFatalError', E_ALL);
        register_shutdown_function('\\Lucinda\\STDERR\\PHPException::fatalError');
        PHPException::setErrorHandler($this);
        set_exception_handler(array($this,"handle"));
        ini_set("display_errors", 0);

        // registers args to be used on demand
        $this->documentDescriptor = $documentDescriptor;
        $this->developmentEnvironment = $developmentEnvironment;
        $this->includePath = $includePath;
        $this->emergencyHandler = $emergencyHandler;
    }

    /**
     * Sets desired display format of rendered response
     *
     * @param string $displayFormat
     */
    public function setDisplayFormat(string $displayFormat): void
    {
        $this->displayFormat = $displayFormat;
    }

    /**
     * Handles errors by delegating to registered storage mediums (if any) then output using display method (if any)
     *
     * @param  \Throwable $exception Encapsulates error information.
     * @throws ConfigurationException
     */
    public function handle(\Throwable $exception): void
    {
        // sets include path
        set_include_path($this->includePath);

        // redirects errors to emergency handler
        PHPException::setErrorHandler($this->emergencyHandler);
        set_exception_handler([$this->emergencyHandler, "handle"]);

        // finds application settings based on XML and development environment
        $application = new Application($this->documentDescriptor, $this->developmentEnvironment);

        // detects route to handle
        $route = $this->getRoute($application, $exception);

        // finds and instances routes based on XML and exception received
        $request = new Request($route, $exception);

        // builds reporters list then reports exception
        $this->runReporters($application, $request);

        // determines response format
        $format = $this->getResponseFormat($application);

        // compiles a response object from content type and http status
        $response = $this->generateResponse($application, $route, $format);

        // locates and runs controller
        $this->runController($application, $request, $response);

        // set up response based on view
        $this->runViewResolver($application, $response, $format);

        // sets http status and commits response to caller
        $response->commit();
    }

    /**
     * Iterates over error reporters and runs them
     *
     * @param Application $application
     * @param Request $request
     * @return void
     */
    protected function runReporters(Application $application, Request $request): void
    {
        $reporters = $application->reporters();
        foreach ($reporters as $className=>$xml) {
            $object = new $className($request, $xml);
            $object->run();
        }
    }

    /**
     * Detects and runs exception controller, if any
     *
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function runController(Application $application, Request $request, Response $response): void
    {
        if ($className = $request->getRoute()->getController()) {
            $object = new $className($application, $request, $response);
            $object->run();
        }
    }

    /**
     * Detects resolver to compile view into response body, if not already written
     *
     * @param Application $application
     * @param Response $response
     * @param Format $format
     * @return void
     */
    protected function runViewResolver(Application $application, Response $response, Format $format): void
    {
        if ($response->getBody()===null) {
            $className = $format->getViewResolver();
            $object = new $className($application, $response);
            $object->run();
        }
    }

    /**
     * Generates preliminary body-less response based on information gathered from route and format
     *
     * @param Application $application
     * @param Route $route
     * @param Format $format
     * @return Response
     * @throws ConfigurationException
     */
    protected function generateResponse(Application $application, Route $route, Format $format): Response
    {
        $charset = $format->getCharacterEncoding();
        $contentType = $format->getContentType().($charset ? "; charset=".$charset : "");

        $view = $route->getView();
        $templateFile = $view ? ($application->getViewsPath()."/".$view) : "";

        $status = $route->getHttpStatus();
        $httpStatus = ($status ?: HttpStatus::INTERNAL_SERVER_ERROR);

        $response = new Response($contentType, $templateFile);
        $response->setStatus($httpStatus);
        return $response;
    }

    /**
     * Gets route to handle
     *
     * @param  Application $application
     * @param  \Throwable  $exception
     * @return Route
     * @throws ConfigurationException
     */
    protected function getRoute(Application $application, \Throwable $exception): Route
    {
        $routes = $application->routes();
        $targetClass = get_class($exception);
        if (isset($routes[$targetClass])) {
            return $routes[$targetClass];
        } elseif (isset($routes[$application->getDefaultRoute()])) {
            return $routes[$application->getDefaultRoute()];
        } else {
            throw new ConfigurationException("Default route matches no route!");
        }
    }

    /**
     * Gets response format to use
     *
     * @param  Application $application
     * @return Format
     * @throws ConfigurationException
     */
    protected function getResponseFormat(Application $application): Format
    {
        $format = $this->displayFormat ? $this->displayFormat : $application->getDefaultFormat();
        $resolvers = $application->resolvers();
        if (isset($resolvers[$format])) {
            return $resolvers[$format];
        } elseif (isset($resolvers[$application->getDefaultFormat()])) {
            return $resolvers[$application->getDefaultFormat()];
        } else {
            throw new ConfigurationException("Default format matches no resolver!");
        }
    }
}
