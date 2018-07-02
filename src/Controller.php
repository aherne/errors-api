<?php
/**
 * Created by PhpStorm.
 * User: aherne
 * Date: 02.07.2018
 * Time: 09:32
 */

namespace Lucinda\Framework\STDERR;


abstract class Controller
{
    protected $application, $route, $view, $reporters;

    public function __construct(Application $application, Route $route, View $view, $reporters) {
        $this->application = $application;
        $this->route = $route;
        $this->view = $view;
        $this->reporters = $reporters;
    }

    public function getReporters() {
        return $this->reporters;
    }
}