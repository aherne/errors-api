<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates error response that will be displayed back to caller
 */
class Response
{
    private $httpStatus;
    private $body;
    private $headers=array();
    private $view;

    /**
     * View constructor.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     */
    public function __construct(Application $application, Request $request){
        $routeInfo = $request->getRoute();
        $this->headers["Content-Type"] = $request->getRoute()->getContentType();
        $this->httpStatus = $request->getRoute()->getHttpStatus();
        if($request->getRoute()->getView()) {
            $this->view = ($application->getViewsPath()."/".$request->getRoute()->getView());
        }
    }
    
    /**
     * Gets relative path of view that contains response body.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Gets response HTTP status
     *
     * @return integer
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Gets response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Gets response headers
     *
     * @return array[string:string]
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Gets value of response header by name
     * 
     * @param string $name
     * @return NULL|string
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])?$this->headers[$name]:null);
    }

    /**
     * Sets response HTTP status
     *
     * @param integer $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;
    }

    /**
     * Sets response body
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
    
    /**
     * Sets response header by name and value.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Sets relative path of view that contains response body.
     *
     * @param string $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }
}

