<?php
namespace Lucinda\Framework\STDERR;

/**
 * Encapsulates error response that will be displayed back to caller
 */
class View
{
    private $httpStatus;
    private $body;
    private $headers=array();
    private $file;

    /**
     * View constructor.
     * @param Application $application Collects information about application
     * @param Route $route Collects information about exception route
     */
    public function __construct(Application $application, Route $route){
        $this->headers["Content-Type"] = $route->getContentType();
        $this->httpStatus = $route->getHttpStatus();
        $this->file = $route->getView();
    }
    
    /**
     * Gets relative path of file that contains response body.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
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
     * Sets relative path of file that contains response body.
     *
     * @param string $view
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}

