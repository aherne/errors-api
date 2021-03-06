<?php
namespace Lucinda\STDERR\Application;

/**
 * Encapsulates a route that matches a handled exception
 */
class Route
{
    private $controller;
    private $view;
    private $httpStatus;
    private $errorType;
    
    /**
     * Detects route info from <exception> tag
     *
     * @param \SimpleXMLElement $info
     */
    public function __construct(\SimpleXMLElement $info)
    {
        $this->controller = (string) $info["controller"];
        $this->view = (string) $info["view"];
        $this->httpStatus = (string) $info["http_status"];
        $this->errorType = (string) $info["error_type"];
    }

    /**
     * Gets controller class name that handles exception handled.
     *
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Gets file that holds what is displayed when error response is rendered.
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
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
