<?php

namespace Lucinda\STDERR\Application;

use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\STDERR\ErrorType;

/**
 * Encapsulates a route that matches a handled exception
 */
class Route extends \Lucinda\MVC\Application\Route
{
    private ?HttpStatus $httpStatus = null;
    private ?ErrorType $errorType = null;

    /**
     * Detects route info from <exception> tag
     *
     * @param  \SimpleXMLElement $info
     * @throws ConfigurationException
     */
    public function __construct(\SimpleXMLElement $info)
    {
        parent::__construct($info);
        $this->setHttpStatus((string) $info["http_status"]);
        $this->setErrorType((string) $info["error_type"]);
    }

    /**
     * Sets http status associated to exception handled.
     *
     * @param  string $httpStatus
     * @throws ConfigurationException
     */
    private function setHttpStatus(string $httpStatus): void
    {
        if (!$httpStatus) {
            return;
        }
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
     * @param  string $errorType
     * @throws ConfigurationException
     */
    private function setErrorType(string $errorType): void
    {
        if (!$errorType) {
            return;
        }
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
}
