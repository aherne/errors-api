<?php

namespace Lucinda\STDERR;

use \Lucinda\MVC\Facet;

/**
 * Blueprint for a user-defined class that decides if it is safe to show errors on screen
 */
interface DisplayErrors extends Facet
{
    function shouldDisplayErrors(): bool;
}
