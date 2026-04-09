<?php

namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\FrontController;
use Lucinda\UnitTest\Validator\Integers;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;
use Test\Lucinda\STDERR\Support\RecordingDisplayErrors;
use Test\Lucinda\STDERR\Support\RecordingFatalErrorResolver;
use Test\Lucinda\STDERR\Support\RecordingReporter;

class FrontControllerTest
{
    private FrontController $object;
    private RecordingReporter $reporter;
    private RecordingFatalErrorResolver $emergencyResolver;
    private RecordingDisplayErrors $displayErrors;

    public function __construct()
    {
        $this->reporter = new RecordingReporter();
        $this->emergencyResolver = new RecordingFatalErrorResolver();
        $this->displayErrors = new RecordingDisplayErrors();
        $this->object = new FrontController(
            __DIR__."/fixtures/root.xml",
            getcwd(),
            $this->reporter,
            $this->emergencyResolver,
            $this->displayErrors
        );
    }

    public function setDisplayFormat()
    {
        $this->object->setDisplayFormat("json");
        $property = new \ReflectionProperty($this->object, "displayFormat");

        return (new Strings((string) $property->getValue($this->object)))->assertEquals("json");
    }

    public function displayErrorsDependency()
    {
        $property = new \ReflectionProperty($this->object, "displayErrors");

        return [
            (new Objects($property->getValue($this->object)))->assertInstanceOf(RecordingDisplayErrors::class),
            (new Integers(spl_object_id($property->getValue($this->object))))->assertEquals(spl_object_id($this->displayErrors))
        ];
    }

    public function handle()
    {
        $outputFile = sys_get_temp_dir()."/front-controller-handle-".uniqid("", true).".json";
        $bootstrap = <<<'PHP'
require getcwd()."/vendor/autoload.php";

$reporter = new \Test\Lucinda\STDERR\Support\RecordingReporter();
$resolver = new \Test\Lucinda\STDERR\Support\RecordingFatalErrorResolver();
$displayErrors = new \Test\Lucinda\STDERR\Support\RecordingDisplayErrors();
$controller = new \Lucinda\STDERR\FrontController(
    getcwd()."/tests/fixtures/root.xml",
    getcwd(),
    $reporter,
    $resolver,
    $displayErrors
);
$controller->handle(new \Test\Lucinda\STDERR\Support\NotFoundException("missing"));
file_put_contents(
    $argv[1],
    json_encode([
        "report_count" => $reporter->getCount(),
        "error_class" => $reporter->getLastError()::class,
        "exit_code" => $controller->getExitCode()
    ], JSON_THROW_ON_ERROR)
);
PHP;
        shell_exec(
            escapeshellarg(PHP_BINARY)
            ." -r "
            .escapeshellarg($bootstrap)
            ." "
            .escapeshellarg($outputFile)
            ." 2>/dev/null"
        );
        $payload = json_decode((string) file_get_contents($outputFile), true);
        @unlink($outputFile);

        return [
            (new Integers((int) ($payload["report_count"] ?? 0)))->assertEquals(1),
            (new Strings($payload["error_class"] ?? ""))->assertEquals(\Test\Lucinda\STDERR\Support\NotFoundException::class),
            (new Integers((int) ($payload["exit_code"] ?? 0)))->assertEquals(4)
        ];
    }

    public function handleFatal()
    {
        $exception = new \RuntimeException("fatal");
        $previous = new \Test\Lucinda\STDERR\Support\FixtureException("root cause");
        $this->object->handleFatal($exception, $previous);

        return [
            (new Integers($this->reporter->getCount()))->assertEquals(1),
            (new Objects($this->reporter->getLastError()))->assertInstanceOf(\RuntimeException::class),
            (new Strings($this->reporter->getLastPrevious()?->getMessage() ?? ""))->assertEquals("root cause"),
            (new Integers($this->emergencyResolver->getCount()))->assertEquals(1)
        ];
    }

    public function getExitCode()
    {
        return (new Integers($this->object->getExitCode()))->assertEquals(1);
    }
}
