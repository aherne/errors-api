<?php
namespace Lucinda\Framework\STDERR;

class View
{
    private $httpStatus;
    private $stream;
    private $file;
    private $headers=array();
    
    public function __construct(Application $application, Route $route){
        $this->headers["Content-Type"] = $route->getContentType();
        $this->httpStatus = $route->getHttpStatus();
        $this->file = $application->getViewsPath()."/".$route->getView().".php";
    }
    
    /**
     * Gets file that contains response body.
     *
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets file that contains response body.
     *
     * @param mixed $view
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
    
    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->headers["Content-Type"];
    }

    /**
     * @return mixed
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * @return mixed
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param mixed $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;
    }

    /**
     * @param mixed $stream
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
    }
    
    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }
    
    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}

