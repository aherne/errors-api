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

    /**
     * Redirects all uncaught exceptions and PHP errors in current application to itself.
     *
     * @param string $documentDescriptor Path to XML file containing your application settings.
     * @param string $developmentEnvironment Development environment application is running into (eg: local, dev, live)
     */
    public function __construct($documentDescriptor="configuration.xml", $developmentEnvironment) {
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
     * Runs framework logic on handled STDERR flow
     *
     * @param \Exception|\Throwable $e Encapsulates error information.
     */
    public function handle($exception) {
        // registers emergency handler
        require_once("EmergencyHandler.php");
        $emergencyHandler = new EmergencyHandler();
        PHPException::setErrorHandler($emergencyHandler);
        set_exception_handler(array($emergencyHandler,"handle"));
        
        // finds application settings based on XML and development environment
        require_once("Application.php");
        $application = new Application($this->documentDescriptor, $this->developmentEnvironment);

        // finds and instances routes based on XML and exception received
        require_once("Request.php");
        $request = new Request($application, $exception, $this->contentType);

        // compiles a view object from content type and http status
        require_once("Response.php");
        $response = new Response($application, $request);

        // runs controller, able to alter reporters
        if($request->getRoute()->getController()) {
            require_once("ControllerFinder.php");
            $cf = new ControllerFinder($application, $request, $response);
            $controller = $cf->getController();
            $controller->run();
        }

        // reports error
        $reporters = $application->getReporters()->toArray();
        foreach($reporters as $reporter) {
            $reporter->report($request);
        }

        // renders output
        $renderers = $application->getRenderers();
        foreach($renderers as $contentType=>$renderer) {
            // content type must be an EXACT match (incl. charset)
            if($contentType == $response->getHeader("Content-Type")) {
                $renderer->render($response);
            }
        }

        exit(); // forces program to end
    }
}
