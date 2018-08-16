<?php
namespace Lucinda\MVC\STDERR;

/**
 * Handler that prevents FrontController handling itself
 */
class EmergencyHandler implements ErrorHandler
{
    public function handle($exception) {
        die($exception->getMessage());
    }
}

