<?php

namespace Test\Lucinda\STDERR\mocks\Resolvers;

use Lucinda\MVC\ViewResolver;
use Lucinda\MVC\ConfigurationException;

class Html extends ViewResolver
{
    public function run(): void
    {
        $view = $this->response->view();
        if ($view->getFile()) {
            if (!file_exists($view->getFile().".html")) {
                throw new ConfigurationException("View file not found");
            }
            ob_start();
            $_VIEW = $view->getData();
            require($view->getFile().".html");
            $output = ob_get_contents();
            ob_end_clean();
            $this->response->setBody($output);
        }
    }
}
