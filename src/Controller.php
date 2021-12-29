<?php
namespace Lucinda\STDERR;

use Lucinda\MVC\Response;
use Lucinda\MVC\Runnable;

/**
 * Encapsulates an abstract MVC controller that routes exceptions that once extended will be useful to:
 * - enable exception-specific reporting policies
 * - allow exception-specific view setup (eg: exceptions that map to multiple views or views using templating)
 * - any other response-changing strategies
 */
abstract class Controller implements Runnable
{
    protected Application $application;
    protected Request $request;
    protected Response $response;

    /**
     * Controller constructor.
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param Response $response Encapsulates response.
     */
    public function __construct(Application $application, Request $request, Response $response)
    {
        $this->application = $application;
        $this->request = $request;
        $this->response = $response;
    }
}
