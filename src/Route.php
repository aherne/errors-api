<?php
namespace Lucinda\Framework\STDERR;

class Route
{
    private $controller;
    private $view;
    private $httpStatus;
    private $reportingStatus;
    private $contentType;
    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
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
    public function getReportingStatus()
    {
        return $this->reportingStatus;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @param mixed $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;
    }

    /**
     * @param mixed $reportingStatus
     */
    public function setReportingStatus($reportingStatus)
    {
        $this->reportingStatus = $reportingStatus;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }


}

