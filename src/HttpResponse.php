<?php

namespace Lucinda\STDERR;

use Lucinda\MVC\Response\Http;
use Lucinda\MVC\Response\HttpStatus;

final class HttpResponse extends Http
{
    public function __construct(HttpStatus $status)
    {
        $this->setStatus($status);
    }
}