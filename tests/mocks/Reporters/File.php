<?php

namespace Test\Lucinda\STDERR\mocks\Reporters;

use Lucinda\STDERR\Reporter;

class File extends Reporter
{
    public function run(): void
    {
        $exception = $this->request->getException();
        $replacements = [
            "%d"=>date("Y-m-d H:i:s"),
            "%e"=>get_class($exception),
            "%f"=>$exception->getFile(),
            "%l"=>$exception->getLine(),
            "%m"=>$exception->getMessage()
        ];
        $format = (string) $this->xml["format"];
        $message = str_replace(array_keys($replacements), array_values($replacements), $format);
        error_log($message."\n", 3, dirname(__DIR__, 3)."/".$this->xml["path"].".log");
    }
}
