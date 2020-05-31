<?php
use Lucinda\STDERR\Controller;

class PathNotFoundController extends Controller
{
    public function run(): void
    {
        $this->response->view()["page"] = $_SERVER["REQUEST_URI"];
    }
}
