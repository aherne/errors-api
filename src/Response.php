<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates error response that will be displayed back to caller
 */
class Response
{
    private $status;
    private $headers=[];
    private $body;
    
    /**
     * Constructs an empty response based on content type
     *
     * @param string $contentType Value of content type header that will be sent in response
     */
    public function __construct($contentType)
    {
        $this->headers["Content-Type"] = $contentType;
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
     * Sets response body
     *
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }
    
    /**
     * Gets response body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
    
    /**
     * Gets or sets response headers will send back to user.
     *
     * @param string $key
     * @param string $value
     * @return string[string]|NULL|string
     */
    public function headers($key="", $value=null)
    {
        if (!$key) {
            return $this->headers;
        } elseif ($value===null) {
            return (isset($this->headers[$key])?$this->headers[$key]:null);
        } else {
            $this->headers[$key] = $value;
        }
    }
    
    /**
     * Redirects to a new location.
     *
     * @param string $location
     * @param boolean $permanent
     * @param boolean $preventCaching
     * @return void
     */
    public static function redirect($location, $permanent=true, $preventCaching=false)
    {
        if ($preventCaching) {
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        header('Location: '.$location, true, $permanent?301:302);
        exit();
    }
        
    /**
     * Commits response to client.
     */
    public function commit()
    {
        // sends headers
        if (!headers_sent()) {
            if ($this->status) {
                header("HTTP/1.1 ".$this->status->getId()." ".$this->status->getDescription());
            }
            
            foreach ($this->headers as $name=>$value) {
                header($name.": ".$value);
            }
        }
        
        // displays body
        echo $this->body;
    }
}
