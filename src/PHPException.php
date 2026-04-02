<?php

namespace Lucinda\STDERR;

/**
 * Exception caught automatically when a PHP error is encountered.
 */
class PHPException
{
    /**
     * Object to which error handling will be delegated to.
     *
     * @var ?ErrorHandler $errorHandler
     */
    private static ?ErrorHandler $errorHandler = null;


    /**
     * Sets object to which error handling will be delegated to.
     *
     * @param ErrorHandler $errorHandler
     */
    public static function setErrorHandler(ErrorHandler $errorHandler): void
    {
        self::$errorHandler = $errorHandler;
    }

    /**
     * Gets object to which error handling are delegated to.
     *
     * @return ?ErrorHandler
     */
    public static function getErrorHandler(): ?ErrorHandler
    {
        return self::$errorHandler;
    }

    /**
     * Function called automatically when a non-fatal PHP error is encountered.
     *
     * @param integer $errorNumber
     * @param string  $message
     * @param string  $file
     * @param integer $line
     * @return bool
     */
    public static function nonFatalError(int $errorNumber, string $message, string $file, int $line): bool
    {
        // respect @ suppression
        if ((error_reporting() & $errorNumber) === 0) {
            return true; // handled: do nothing
        }
        if (!self::$errorHandler) {
            // PHP will log/display depending on ini
            return false;
        }

        $e = new \ErrorException($message, 0, $errorNumber, $file, $line);
        self::$errorHandler->handle($e);
        return true;
    }

    /**
     * Function called automatically when a fatal PHP error is encountered.
     */
    public static function fatalError(): void
    {
        $error = error_get_last();
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!$error || !self::$errorHandler || !in_array($error['type'], $fatalTypes, true)) {
            // PHP will log/display depending on ini
            return;
        }

        $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        self::$errorHandler->handleFatal($e);
    }
}
