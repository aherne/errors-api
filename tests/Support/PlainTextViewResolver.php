<?php

namespace Test\Lucinda\STDERR\Support;

use Lucinda\MVC\Response\View;
use Lucinda\MVC\Response\ViewResolver;

class PlainTextViewResolver implements ViewResolver
{
    public function resolve(View $view): string
    {
        $contents = file_get_contents($view->getFile());
        if ($contents === false) {
            return "";
        }

        foreach ($view->getData() as $key => $value) {
            $contents = str_replace("{{".$key."}}", (string) $value, $contents);
        }

        return $contents;
    }
}
