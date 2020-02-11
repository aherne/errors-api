<?php
use Lucinda\STDERR\Controller;

class PathNotFoundController extends Controller
{
    public function run()
    {
        $this->response->view()->data("page", $_SERVER["REQUEST_URI"]);
    }
}
