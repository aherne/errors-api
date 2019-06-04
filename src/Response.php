<?php
namespace Lucinda\MVC\STDERR;

require_once("response/ResponseStatus.php");
require_once("response/ResponseStream.php");

/**
 * Encapsulates error response that will be displayed back to caller
 */
class Response
{
    private $status;
    private $outputStream;
    private $headers=[];
    private $attributes = [];
    private $view;
    private $isDisabled;

    /**
     * View constructor.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param string $customContentType Content type of rendered response specifically signalled to FrontController.
     */
    public function __construct(Application $application, Request $request, $customContentType){
        $this->outputStream	= new ResponseStream();
        $this->setStatus($request->getRoute()->getHttpStatus());        
        $this->setView($request->getRoute()->getView()?($application->getViewsPath()."/".$request->getRoute()->getView()):null);
        $this->headers["Content-Type"]= $this->getContentType($application, $request, $customContentType);
    }
    
    /**
     * Gets response stream to work on.
     *
     * @return ResponseStream
     */
    public function getOutputStream() {
        return $this->outputStream;
    }
    
    /**
     * Sets content type header based on ingridients
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param string $customContentType Content type of rendered response specifically signalled to FrontController.
     */
    private function getContentType(Application $application, Request $request, $customContentType) {
        $currentContentType = "";
        if($customContentType) {
            $currentContentType = $customContentType;
        } else if($request->getRoute()->getContentType()) {
            $currentContentType = $request->getRoute()->getContentType();
        } else {
            $currentContentType = $application->getDefaultContentType();
        }
        
        $renderers = $application->renderers();
        foreach($renderers as $contentType=>$renderer) {
            if(strpos($contentType, $currentContentType) === 0) {
                return $contentType;
            }
        }
        
        throw new Exception("Content type not supported!");
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
     * Sets response HTTP status
     *
     * @param integer $code
     */
    public function setStatus($code)
    {
        $this->status = new ResponseStatus($code);
    }

    /**
     * Gets response HTTP status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Gets or sets response headers will send back to user.
     *
     * @param string $key
     * @param string $value
     * @return string[string]|NULL|string
     */
    public function headers($key="", $value=null) {
        if(!$key) return $this->headers;
        else if($value===null) return (isset($this->headers[$key])?$this->headers[$key]:null);
        else $this->headers[$key] = $value;
    }
    
    /**
     * Gets or sets data that will be sent to views.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed[string]|NULL|mixed
     */
    public function attributes($key="", $value=null) {
        if(!$key) return $this->attributes;
        else if($value===null) return (isset($this->attributes[$key])?$this->attributes[$key]:null);
        else $this->attributes[$key] = $value;
    }
    
    /**
     * Redirects to a new location.
     *
     * @param string $location
     * @param boolean $permanent
     * @param boolean $preventCaching
     * @return void
     */
    public function redirect($location, $permanent=true, $preventCaching=false) {
        if($preventCaching) {
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        header('Location: '.$location, true, $permanent?301:302);
        exit();
    }
    
    /**
     * Disables response. A disabled response will output nothing.
     */
    public function disable() {
        $this->isDisabled = true;
    }
    
    /**
     * Checks if response is disabled.
     *
     * @return boolean
     */
    public function isDisabled() {
        return $this->isDisabled;
    }
        
    /**
     * Commits response to client.
     */
    public function commit() {
        // do not display anything, if headers have already been sent
        if(!headers_sent() && $this->status) {
            header("HTTP/1.1 ".$this->status->getId()." ".$this->status->getDescription());     
        }
        
        if(!$this->isDisabled) {
            // sends headers   
            foreach($this->headers as $name=>$value) {
                header($name.": ".$value);
            }
            
            // show output
            echo $this->outputStream->get();
        }
    }
}

