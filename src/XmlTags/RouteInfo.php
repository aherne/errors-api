<?php

namespace Lucinda\STDERR\XmlTags;

use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\MVC\XmlReader\Element;
use Lucinda\STDERR\ErrorType;

/**
 * Encapsulates a route that matches a handled exception
 */
class RouteInfo extends \Lucinda\MVC\XmlTags\RouteInfo
{
    private ?HttpStatus $httpStatus = null;
    private ?ErrorType $errorType = null;
    private ?int $exitCode = null;

    /**
     * Detects route info from <exception> tag
     *
     * @param  Element $element
     * @throws ConfigurationException
     */
    public function __construct(Element $element)
    {
        $this->controller = "";
        $this->view = "";
        $this->format = "";
        parent::__construct($element);
        $attributes = $element->getAttributes();
        $this->setHttpStatus($attributes);
        $this->setErrorType($attributes);
        $this->setExitCode($attributes);
    }

    /**
     * Sets http status associated to exception handled.
     *
     * @param  array $attributes
     * @throws ConfigurationException
     */
    private function setHttpStatus(array $attributes): void
    {
        if (empty($attributes["http_status"])) {
            return;
        }

        $httpStatus = $attributes["http_status"];
        $cases = HttpStatus::cases();
        foreach ($cases as $case) {
            if (str_starts_with($case->value, $httpStatus)) {
                $this->httpStatus = $case;
                return;
            }
        }
        throw new ConfigurationException("Invalid http status: ".$httpStatus);
    }

    /**
     * Gets HTTP status associated to exception handled.
     *
     * @return ?HttpStatus
     */
    public function getHttpStatus(): ?HttpStatus
    {
        return $this->httpStatus;
    }

    /**
     * Sets HTTP status associated to exception handled.
     *
     * @param  array $attributes
     * @throws ConfigurationException
     */
    private function setErrorType(array $attributes): void
    {
        if (empty($attributes["error_type"])) {
            return;
        }

        $errorType = $attributes["error_type"];
        if ($case = ErrorType::tryFrom($errorType)) {
            $this->errorType = $case;
        } else {
            throw new ConfigurationException("Invalid error type: ".$errorType);
        }
    }

    /**
     * Gets error type associated to exception handled.
     *
     * @return ?ErrorType
     */
    public function getErrorType(): ?ErrorType
    {
        return $this->errorType;
    }

    private function setExitCode(array $attributes): void
    {
        $this->exitCode = (int) ($attributes["exit_code"]??1);
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
