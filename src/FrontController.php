<?php
namespace Lucinda\STDERR;

use Lucinda\MVC\Application\Format;
use Lucinda\MVC\Response;
use Lucinda\STDERR\Application\Route;
use Lucinda\MVC\ConfigurationException;

/**
 * Error handler that bootstraps all uncaught exceptions and PHP errors as a STDERR front controller that feeds on
 * exception instances instead of requests.
 */
class FrontController implements ErrorHandler
{
    const DEFAULT_HTTP_STATUS = 500;
    
    private $displayFormat;
    private $documentDescriptor;
    private $developmentEnvironment;
    private $includePath;
    private $emergencyHandler;

    /**
     * Redirects all uncaught exceptions and PHP errors in current application to itself.
     *
     * @param string $documentDescriptor Path to XML file containing your application settings.
     * @param string $developmentEnvironment Development environment application is running into (eg: local, dev, live)
     * @param string $includePath Absolute root path where reporters / resolvers / controllers / views should be located
     * @param ErrorHandler $emergencyHandler Handler to use if an error occurs while FrontController handles an exception
     */
    public function __construct(string $documentDescriptor, string $developmentEnvironment, string $includePath, ErrorHandler $emergencyHandler)
    {
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
     * @param \Throwable $exception Encapsulates error information.
     */
    public function handle(\Throwable $exception): void
    {
        // sets include path
        set_include_path($this->includePath);
        
        // redirects errors to emergency handler
        PHPException::setErrorHandler($this->emergencyHandler);
        set_exception_handler(array($this->emergencyHandler,"handle"));
        
        // finds application settings based on XML and development environment
        $application = new Application($this->documentDescriptor, $this->developmentEnvironment);
        
        // finds and instances routes based on XML and exception received
        $request = new Request($this->getRoute($application, $exception), $exception);
        
        // builds reporters list then reports exception
        $reporters = $application->reporters();
        foreach ($reporters as $className=>$xml) {
            $object = new $className($request, $xml);
            $object->run();
        }
        
        // compiles a response object from content type and http status
        $format = $this->getResponseFormat($application);
        $response = new Response($this->getContentType($format), $this->getTemplateFile($application, $request));
        $response->setStatus($this->getResponseStatus($request->getRoute()));
        
        // locates and runs controller
        $className = $request->getRoute()->getController();
        if ($className) {
            $object = new $className($application, $request, $response);
            $object->run();
        }
        
        // set up response based on view
        if ($response->getBody()===null) {
            $className = $format->getViewResolver();
            $object = new $className($application, $response);
            $object->run();
        }
        
        // commits response to caller
        $response->commit();
    }
    
    /**
     * Gets response http status code
     * 
     * @param Route $route
     * @return int
     */
    private function getResponseStatus(Route $route): int
    {
        return ($route->getHttpStatus()?$route->getHttpStatus():self::DEFAULT_HTTP_STATUS);
    }
    
    /**
     * Gets response template file
     *
     * @param Application $application
     * @param Request $request
     * @return string
     */
    private function getTemplateFile(Application $application, Request $request): string
    {
        return ($request->getRoute()->getView()?($application->getViewsPath()."/".$request->getRoute()->getView()):"");
    }
    
    /**
     * Gets response content type
     *
     * @param Format $format
     * @return string
     */
    private function getContentType(Format $format): string
    {
        return $format->getContentType().($format->getCharacterEncoding()?"; charset=".$format->getCharacterEncoding():"");
    }
    
    /**
     * Gets route to handle
     * 
     * @param Application $application
     * @param \Throwable $exception
     * @return Route
     * @throws ConfigurationException
     */
    private function getRoute(Application $application, \Throwable $exception): Route
    {
        $routes = $application->routes();
        $targetClass = get_class($exception);
        if (isset($routes[$targetClass])) {
            return $routes[$targetClass];
        } else {
            if (isset($routes[$application->getDefaultRoute()])) {
                return $routes[$application->getDefaultRoute()];
            } else {
                throw new ConfigurationException("Default route matches no route!");
            }
        }
    }
    
    /**
     * Gets response format to use
     * 
     * @param Application $application
     * @return Format
     * @throws ConfigurationException
     */
    private function getResponseFormat(Application $application): Format
    {
        $format = $this->displayFormat?$this->displayFormat:$application->getDefaultFormat();
        $resolvers = $application->resolvers();
        if (isset($resolvers[$format])) {
            return $resolvers[$format];
        } else {
            if (isset($resolvers[$application->getDefaultFormat()])) {
                return $resolvers[$application->getDefaultFormat()];
            } else {
                throw new ConfigurationException("Default format matches no resolver!");
            }
        }
    }
}
