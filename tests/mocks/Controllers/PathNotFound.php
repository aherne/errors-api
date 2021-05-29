<?php
namespace Test\Lucinda\STDERR\mocks\Controllers;

use Lucinda\STDERR\Controller;

class PathNotFound extends Controller
{
    public function run(): void
    {
        $this->response->view()["page"] = $_SERVER["REQUEST_URI"];
    }
}
