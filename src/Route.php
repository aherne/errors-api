<?php
namespace Lucinda\Framework\STDERR;

/**
 * Encapsulates a route that matches a thrown exception
 */
class Route
{
    private $controller;
    private $view;
    private $httpStatus;
    private $errorType;
    private $contentType;
    private $exception;

    /**
     * Gets controller class name that handles exception thrown.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Gets file that holds what is displayed when error response is rendered.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Gets HTTP status associated to exception thrown.
     *
     * @return integer
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Gets error type associated to exception thrown.
     * 
     * @return ErrorType
     */
    public function getErrorType()
    {
        return $this->errorType;
    }

    /**
     * Gets content type associated to exception thrown.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sets controller class name that handles exception thrown.
     *
     * @param mixed $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Sets file that holds what is displayed when error response is rendered.
     *
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Sets HTTP status associated to exception thrown.
     *
     * @param integer $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;
    }

    /**
     * Sets error type associated to exception thrown.
     *
     * @param integer $errorType
     */
    public function setErrorType($errorType)
    {
        $this->errorType = $errorType;
    }

    /**
     * Sets content type associated to exception thrown.
     *
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }
}

