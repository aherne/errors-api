<?php

namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\PHPException;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Integers;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;
use Test\Lucinda\STDERR\Support\FileRecordingErrorHandler;
use Test\Lucinda\STDERR\Support\RecordingErrorHandler;

class PHPExceptionTest
{
    private RecordingErrorHandler $handler;

    public function __construct()
    {
        $this->handler = new RecordingErrorHandler();
    }

    public function setErrorHandler()
    {
        PHPException::setErrorHandler($this->handler);

        return (new Objects(PHPException::getErrorHandler()))->assertInstanceOf(RecordingErrorHandler::class);
    }

    public function getErrorHandler()
    {
        PHPException::setErrorHandler($this->handler);

        return (new Integers(spl_object_id(PHPException::getErrorHandler())))->assertEquals(spl_object_id($this->handler));
    }

    public function nonFatalError()
    {
        PHPException::setErrorHandler($this->handler);
        $result = PHPException::nonFatalError(E_USER_WARNING, "non fatal", __FILE__, __LINE__);

        return [
            (new Booleans($result))->assertTrue(),
            (new Integers($this->handler->getHandleCount()))->assertEquals(1),
            (new Objects($this->handler->getLastHandled()))->assertInstanceOf(\ErrorException::class),
            (new Strings($this->handler->getLastHandled()->getMessage()))->assertEquals("non fatal")
        ];
    }

    public function fatalError()
    {
        $outputFile = sys_get_temp_dir()."/php-exception-fatal-".uniqid("", true).".json";
        $bootstrap = <<<'PHP'
require getcwd()."/vendor/autoload.php";
\Lucinda\STDERR\PHPException::setErrorHandler(
    new \Test\Lucinda\STDERR\Support\FileRecordingErrorHandler($argv[1])
);
register_shutdown_function([\Lucinda\STDERR\PHPException::class, "fatalError"]);
trigger_error("fatal shutdown", E_USER_ERROR);
PHP;
        $output = shell_exec(
            escapeshellarg(PHP_BINARY)
            ." -r "
            .escapeshellarg($bootstrap)
            ." "
            .escapeshellarg($outputFile)
            ." 2>&1"
        );
        $payload = json_decode((string) file_get_contents($outputFile), true);

        @unlink($outputFile);

        return [
            (new Strings($payload["mode"] ?? ""))->assertEquals("fatal"),
            (new Strings($payload["class"] ?? ""))->assertEquals(\ErrorException::class),
            (new Strings($payload["message"] ?? ""))->assertContains("fatal shutdown"),
            (new Strings((string) $output))->assertContains("fatal shutdown")
        ];
    }
}
