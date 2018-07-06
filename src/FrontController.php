<?php
namespace Lucinda\Framework\STDERR;

require_once("ErrorHandler.php");
require_once("PHPException.php");

class FrontController implements ErrorHandler
{
    private $contentType;
    private $documentDescriptor;
    private $developmentEnvironment;
    
    public function __construct($documentDescriptor="configuration.xml", $developmentEnvironment) {
        // sets up system to track errors
        error_reporting(E_ALL);
        set_error_handler('\\Lucinda\\Framework\\STDERR\\PHPException::nonFatalError', E_ALL);
        register_shutdown_function('\\Lucinda\\Framework\\STDERR\\PHPException::fatalError');        
        PHPException::setErrorHandler($this);
        set_exception_handler(array($this,"handle"));
        ini_set("display_errors",0);
        
        // registers args to be used on demand
        $this->documentDescriptor = $documentDescriptor;
        $this->developmentEnvironment = $developmentEnvironment;
    }
    
    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }
    
    public function handle($exception) {
        $application = null;
        try {
            // finds application settings based on XML and development environment
            require_once("Application.php");
            $application = new Application($this->documentDescriptor, $this->developmentEnvironment);
            
            // finds and instances reporters based on XML and development environment
            require_once("ErrorReportersFinder.php");
            $erp = new ErrorReportersFinder($application, $this->developmentEnvironment);
            $reporters = $erp->getReporters();
            
            // finds and instances routes based on XML and exception received
            require_once("RouteFinder.php");
            $rf = new RouteFinder($application, $exception, $this->contentType);
            $route = $rf->getRoute();

            // compiles a view object from content type and http status
            require_once("View.php");
            $view = new View($application, $route);

            // passes View object to Controller
            if($route->getController()) {
                require_once("ControllerFinder.php");
                $cf = new ControllerFinder($application, $route, $view, $reporters);
                $controller = $cf->getController();
                $reporters = $controller->run($exception);
            }

            // report
            if($route->getErrorType()!=ErrorType::NONE) {
                foreach($reporters as $reporter) {
                    $reporter->report($exception, $route->getErrorType());
                }
            }

            // render
            require_once("ErrorRendererFinder.php");
            $erf = new ErrorRendererFinder($application, $view->getContentType());
            $renderer = $erf->getRenderer();
            $renderer->render($view);
        } catch(Exception $internalError) {
            if($application && $application->getDisplayErrors()) {
                var_dump($internalError);
            }
        }

        die(); // prevent further catch cycle
    }
}
