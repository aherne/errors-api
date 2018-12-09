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
     * @param string $customContentType Content type of rendered response specifically signalled to FrontController.
     */
    public function __construct(Application $application, Request $request, $customContentType){
        $this->setContentType($application, $request, $customContentType);
        $this->setHttpStatus($request->getRoute()->getHttpStatus());        
        $this->setView($request->getRoute()->getView()?($application->getViewsPath()."/".$request->getRoute()->getView()):null);
    }
    
    /**
     * Sets content type header based on ingridients
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param string $customContentType Content type of rendered response specifically signalled to FrontController.
     */
    private function setContentType(Application $application, Request $request, $customContentType) {
        $currentContentType = "";
        if($customContentType) {
            $currentContentType = $customContentType;
        } else if($request->getRoute()->getContentType()) {
            $currentContentType = $request->getRoute()->getContentType();
        } else {
            $currentContentType = $application->getDefaultContentType();
        }
        
        $renderers = $application->getRenderers();
        foreach($renderers as $contentType=>$renderer) {
            if(strpos($contentType, $currentContentType) === 0) {
                $this->headers["Content-Type"] = $contentType;
            }
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
     * Sets relative path of view that contains response body.
     *
     * @param string $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }
}

