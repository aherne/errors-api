<?php

namespace Lucinda\STDERR;

use Lucinda\MVC\Response\Basic as Response;
use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Controller\ViewAware;
use Lucinda\MVC\FacetRegistry;
use Lucinda\MVC\ReflectionInjector;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\MVC\Response\View;
use Lucinda\MVC\Response\Http;
use Lucinda\MVC\Response\Console;
use Lucinda\MVC\Service\ViewDetector;
use Lucinda\STDERR\Service\ContentTypeDetector;
use Lucinda\STDERR\Service\ValidatedRequest;
use Lucinda\STDERR\XmlTags\ResolverInfo;
use Lucinda\STDERR\XmlTags\RouteInfo;
use Throwable;

/**
 * Error handler that bootstraps all uncaught exceptions and PHP errors as a STDERR front controller that feeds on
 * exception instances instead of requests.
 */
final class FrontController implements ErrorHandler
{
    const DEFAULT_EXIT_CODE = 1;

    private ?string $displayFormat = null;
    private string $documentDescriptor;
    private string $includePath;
    private ErrorReporter $reporter;
    private FatalErrorResolver $emergencyResolver;
    protected FacetRegistry $facetRegistry;
    protected ReflectionInjector $reflectionInjector;
    private bool $displayErrors;

    private bool $handling = false;
    private int $exitCode = self::DEFAULT_EXIT_CODE;

    /**
     * Redirects all uncaught exceptions and PHP errors in current application to itself.
     *
     * @param string       $documentDescriptor     Path to XML file containing your application settings.
     * @param string       $includePath            Absolute root path where reporters / resolvers / controllers / views should be located
     * @param ErrorReporter $errorReporter
     * @param FatalErrorResolver $emergencyResolver       Handler to use if an error occurs while FrontController handles an exception
     * @param bool $displayErrors 
     */
    public function __construct(
        string $documentDescriptor,
        string $includePath,
        ErrorReporter $reporter,
        FatalErrorResolver $emergencyResolver,
        bool $displayErrors = false
    ) {
        // registers args to be used on demand
        $this->documentDescriptor = $documentDescriptor;
        $this->includePath = $includePath;
        $this->reporter = $reporter;
        $this->emergencyResolver = $emergencyResolver;
        $this->displayErrors = $displayErrors;
        $this->facetRegistry = new FacetRegistry();
        $this->reflectionInjector = new ReflectionInjector($this->facetRegistry);
        
        $this->startListening();
    }

    private function startListening(): void
    {
        // sets up system to track errors
        ini_set("display_errors", 0);
        ini_set('log_errors', '1');              // ensure logging is on    
        error_reporting(E_ALL);
        set_exception_handler([$this,"handle"]);
        set_error_handler('\\Lucinda\\STDERR\\PHPException::nonFatalError', E_ALL);
        register_shutdown_function('\\Lucinda\\STDERR\\PHPException::fatalError');
        PHPException::setErrorHandler($this);
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
        // avoid recursive reentry while already handling an exception
        if (!$this->startHandling()) {
            return;
        }

        try {
            // sets include path
            set_include_path($this->includePath);

            // reports immediately
            $this->reporter->report($exception);

            // finds application settings based on XML and development environment
            $application = new Application($this->documentDescriptor);
            $this->facetRegistry->put($application->getApplicationInfo());

            // detects route to handle
            $requestValidator = new ValidatedRequest($application, $exception, $this->displayFormat ?? "");
            $routeInfo = $application->getRoutes($requestValidator->getRoute());
            $resolverInfo = $application->getResolvers($requestValidator->getFormat());

            // finds and instances routes based on XML and exception received
            $request = new Request($routeInfo, $exception);
            $this->facetRegistry->put($request);

            // compiles a response object from content type and http status
            $response = $this->generateResponse($application, $routeInfo, $resolverInfo);

            // locates and runs controller
            $filledView = $this->runController($request);
            $viewDetector = new ViewDetector($application, $requestValidator, $filledView);
            if ($view = $viewDetector->getView()) {
                $this->runViewResolver($resolverInfo, $response, $view);
            }

            // sets http status and commits response to caller
            $response->run();

            if ($response instanceof Console) {
                $this->exitCode = $response->getExitCode();
            }
        } catch (\Throwable $apiException) {
            $this->handleFatal($apiException, $exception);    
        } finally {
            $this->handling = false;
        }
    }

    public function handleFatal(Throwable $exception, ?Throwable $previous = null): void
    {
        try {
            $this->reporter->report($exception, $previous);

            $body = $this->emergencyResolver->resolve($exception, $previous);

            $response = null;
            if (PHP_SAPI !== 'cli') {
                $response = new Http();
                $response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
            } else {
                $response = new Console(self::DEFAULT_EXIT_CODE, STDERR);
            }
            $response->setBody($body);
            $response->run();

            if ($response instanceof Console) {
                $this->exitCode = $response->getExitCode();
            }
        } catch (\Throwable $exception) {
            // swallow: emergency path must never recurse
        }    
    }

    /**
     * Detects and runs exception controller, if any
     *
     * @param Request $request
     * @param Response $response
     * @return ?View
     */
    protected function runController(Request $request): ?View
    {
        if ($className = $request->getRoute()->getController()) {
            $object = $this->reflectionInjector->create($className);
            if ($object instanceof ViewAware) {
                return $object->run();
            } else {
                $object->run();
            }
        }
        return null;
    }

    /**
     * Signals that handling is active. Returns false if handling already started
     * 
     * @return bool
     */
    private function startHandling(): bool
    {
        if ($this->handling) {
            return false;
        }
        $this->handling = true;
        return true;
    }

    /**
     * Detects resolver to compile view into response body, if not already written
     *
     * @param Application $application
     * @param Response $response
     * @param ResolverInfo $resolverInfo
     * @return void
     */
    protected function runViewResolver(
        ResolverInfo $resolverInfo,
        Response $response,
        View $view
    ): void {
        $resolver = $this->reflectionInjector->create($resolverInfo->getViewResolver());
        $response->resolve($view, $resolver);
    }

    /**
     * Generates preliminary body-less response based on information gathered from route and format
     *
     * @param Application $application
     * @param RouteInfo $route
     * @param ResolverInfo $resolverInfo
     * @return Response
     * @throws ConfigurationException
     */
    protected function generateResponse(Application $application, RouteInfo $route, ResolverInfo $resolverInfo): Response
    {
        if (PHP_SAPI !== 'cli') {
            $detector = new ContentTypeDetector($resolverInfo);
            $contentType = $detector->getContentType();

            $status = $route->getHttpStatus();
            $httpStatus = ($status ?: HttpStatus::INTERNAL_SERVER_ERROR);

            $response = new Http();
            $response->setStatus($status);
            $response->setHeader("Content-Type", $contentType);
            return $response;
        } else {
            return new Console($route->getExitCode()?:self::DEFAULT_EXIT_CODE, STDERR);
        }
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
