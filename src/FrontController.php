<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorHandler.php");
require_once("PHPException.php");
require_once("Exception.php");

/**
 * Error handler that bootstraps all uncaught exceptions and PHP errors as a STDERR front controller that feeds on
 * exception instances instead of requests.
 */
class FrontController implements ErrorHandler
{
    private $contentType;
    private $documentDescriptor;
    private $developmentEnvironment;
    private $includePath;
    private $emergencyHandler;

    /**
     * Redirects all uncaught exceptions and PHP errors in current application to itself.
     *
     * @param string $documentDescriptor Path to XML file containing your application settings.
     * @param string $developmentEnvironment Development environment application is running into (eg: local, dev, live)
     * @param string $includePath Absolute root path where reporters / renderers / controllers / views should be located 
     * @param ErrorHandler $emergencyHandler Handler to use if an error occurs while FrontController handles an exception 
     */
    public function __construct($documentDescriptor, $developmentEnvironment, $includePath, ErrorHandler $emergencyHandler) {
        // sets up system to track errors
        error_reporting(E_ALL);
        set_error_handler('\\Lucinda\\MVC\\STDERR\\PHPException::nonFatalError', E_ALL);
        register_shutdown_function('\\Lucinda\\MVC\\STDERR\\PHPException::fatalError');
        PHPException::setErrorHandler($this);
        set_exception_handler(array($this,"handle"));
        ini_set("display_errors",0);
        
        // registers args to be used on demand
        $this->documentDescriptor = $documentDescriptor;
        $this->developmentEnvironment = $developmentEnvironment;
        $this->includePath = $includePath;
        $this->emergencyHandler = $emergencyHandler;
    }

    /**
     * Sets desired content type of rendered response
     *
     * @param string $contentType
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    /**
     * {@inheritDoc}
     * @see ErrorHandler::handle()
     */
    public function handle($exception) {
        // redirects errors to emergency handler
        PHPException::setErrorHandler($this->emergencyHandler);
        set_exception_handler(array($this->emergencyHandler,"handle"));
        
        // finds application settings based on XML and development environment
        require_once("Application.php");
        $application = new Application($this->documentDescriptor, $this->developmentEnvironment, $this->includePath);
		
        // finds and instances routes based on XML and exception received
        require_once("Request.php");
        $request = new Request($application, $exception);
		
		// builds reporters list then reports exception
		require_once("ErrorReporter.php");
		require_once("locators/ReportersLocator.php");
		$locator = new ReportersLocator($application);
		$reportersList = $locator->getReporters();
        foreach($reportersList as $reporter) {
			$reporter->report($request);
		}

        // compiles a view object from content type and http status
        require_once("Response.php");
        $response = new Response($application, $request, $this->contentType);
		
        // runs controller, able to customize response
        if($request->getRoute()->getController()) {
			require_once("Controller.php");
            require_once("locators/ControllerLocator.php");
            $locator = new ControllerLocator($application, $request, $response);
            $controller = $locator->getController();
            $controller->run();
        }

        // renders response to output stream
		require_once("ErrorRenderer.php");
		require_once("locators/RendererLocator.php");
		$locator = new RendererLocator($application, $response);
        $renderer = $locator->getRenderer();
        $renderer->render($response);

        // commits response to caller
        $response->commit();
        
        exit(); // forces program to end
    }
}
