<?php

namespace Lucinda\STDERR\Service;

use Lucinda\MVC\RequestValidator;
use Lucinda\STDERR\Application;

/**
 * Binds information in Request and Application objects in order to detect final route info
 */
final class ValidatedRequest implements RequestValidator
{
    private string $page;
    private string $format;

    /**
     * Bootstraps binding process
     * 
     * @param Application $application
     * @param \Throwable $handledThrowable
     * @param string $overridenFormat
     */
    public function __construct(Application $application, \Throwable $handledThrowable, string $overridenFormat)
    {
        $this->setRoute($application, $handledThrowable);
        $this->setFormat($application, $overridenFormat);
    }

    private function setRoute(Application $application, \Throwable $handledThrowable): void
    {
        $targetClass = get_class($handledThrowable);
        $defaultRoute = $application->getApplicationInfo()->getDefaultRoute();
        if ($route = $application->getRoutes($targetClass)) {
            $this->page = $route->getID();
        } elseif ($route = $application->getRoutes($defaultRoute)) {
            $this->page = $route->getID();
        } else {
            throw new ValidationFailedException("Default route matches no route!");
        }
    }

    private function setFormat(Application $application, string $overridenFormat): void
    {
        $defaultFormat = $application->getApplicationInfo()->getDefaultFormat();
        $format = $overridenFormat ?: $defaultFormat;

        if ($resolver = $application->getResolvers($format)) {
            $this->format = $resolver->getFormat();
        } elseif ($resolver = $application->getResolvers($defaultFormat)) {
            $this->format = $resolver->getFormat();
        } else {
            throw new ValidationFailedException("Default format matches no resolver!");
        }
    }

    /**
     * Gets final route detected after validation
     * 
     * @return string
     */
    public function getRoute(): string
    {
        return $this->page;
    }

    /**
     * Gets final response format (extension) after validation
     * 
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }
}