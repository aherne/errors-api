<?php
namespace Lucinda\MVC\STDERR;

/**
 * Error handler that bootstraps all uncaught exceptions and PHP errors as a STDERR front controller that feeds on
 * exception instances instead of requests.
 */
class FrontController implements ErrorHandler
{
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
    public function __construct($documentDescriptor, $developmentEnvironment, $includePath, ErrorHandler $emergencyHandler)
    {
        // sets up system to track errors
        error_reporting(E_ALL);
        set_error_handler('\\Lucinda\\MVC\\STDERR\\PHPException::nonFatalError', E_ALL);
        register_shutdown_function('\\Lucinda\\MVC\\STDERR\\PHPException::fatalError');
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
    public function setDisplayFormat($displayFormat)
    {
        $this->displayFormat = $displayFormat;
    }

    /**
     * {@inheritDoc}
     * @see ErrorHandler::handle()
     */
    public function handle($exception)
    {
        // sets include path
        set_include_path($this->includePath);
        
        // redirects errors to emergency handler
        PHPException::setErrorHandler($this->emergencyHandler);
        set_exception_handler(array($this->emergencyHandler,"handle"));
        
        // finds application settings based on XML and development environment
        $application = new Application($this->documentDescriptor, $this->developmentEnvironment);
        
        // finds and instances routes based on XML and exception received
        $routes = $application->routes();
        $targetClass = get_class($exception);
        $request = new Request((isset($routes[$targetClass])?$routes[$targetClass]:$routes[""]), $exception);
        
        // builds reporters list then reports exception
        $reporters = $application->reporters();
        foreach($reporters as $className=>$xml) {
            $locator = new ReporterLocator($application, $className);
            $className = $locator->getClassName();            
            $object = new $className($request, $xml);
            $object->run();
        }        

        // compiles a view object
        $view = new View($request->getRoute()->getView()?($application->getViewsPath()."/".$request->getRoute()->getView()):null);
        
        // locates and runs controller
        $locator = new ControllerLocator($application, $request);
        $className = $locator->getClassName();
        if ($className) {
            $object = new $className($application, $request, $view);
            $object->run();
        }
        
        // detects response format
        $format = $application->formats($this->displayFormat?$this->displayFormat:$application->getDefaultFormat());
        
        // compiles a response object from content type and http status
        $response = new Response($format->getContentType().($format->getCharacterEncoding()?"; charset=".$format->getCharacterEncoding():""));
        $response->setStatus($request->getRoute()->getHttpStatus());
        
        // set up response based on view
        $locator = new ViewResolverLocator($application, $format);
        $className = $locator->getClassName();
        $object = new $className($application, $view, $response);
        $object->run();
        
        // commits response to caller
        $response->commit();
        
        // forces program to end
        exit(); 
    }
}
