<?php
namespace Lucinda\STDERR\Application;

/**
 * Encapsulates a route that matches a handled exception
 */
class Route extends \Lucinda\MVC\Application\Route
{
    private $httpStatus;
    private $errorType;
    
    /**
     * Detects route info from <exception> tag
     *
     * @param \SimpleXMLElement $info
     */
    public function __construct(\SimpleXMLElement $info)
    {
        parent::__construct($info);
        $this->httpStatus = (string) $info["http_status"];
        $this->errorType = (string) $info["error_type"];
    }

    /**
     * Gets HTTP status associated to exception handled.
     *
     * @return string
     */
    public function getHttpStatus(): string
    {
        return $this->httpStatus;
    }
    
    /**
     * Gets error type associated to exception handled.
     *
     * @return string One of possible values of ErrorType enum
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }
}
