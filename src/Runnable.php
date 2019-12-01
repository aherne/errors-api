<?php
namespace Lucinda\STDERR;


/**
 * Defines blueprints of an object that can be ran by FrontController
 */
interface Runnable
{
    /**
     * Executes logic of a Runnable
     */
    public function run(): void;
}
