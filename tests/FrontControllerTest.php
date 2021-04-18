<?php
namespace Test\Lucinda\STDERR;

use Lucinda\STDERR\FrontController;
use Lucinda\UnitTest\Result;
use Lucinda\MVC\ConfigurationException;

class FrontControllerTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new FrontController(__DIR__."/configuration.xml", "local", dirname(__DIR__), new MockEmergencyHandler());
    }

    public function setDisplayFormat()
    {
        $this->object->setDisplayFormat("html");
        return new Result(true);
    }
        

    public function handle()
    {
        $results = [];
        
        ob_start();
        $this->object->handle(new ConfigurationException("asdf"));
        $contents = ob_get_contents();
        ob_end_clean();
        
        $results[] = new Result($contents==file_get_contents(__DIR__."/mocks/views/500.html"), "tested main route");
        
        ob_start();
        $_SERVER["REQUEST_URI"] = "/test";
        $this->object->handle(new PathNotFoundException("asdf"));
        $contents = ob_get_contents();
        ob_end_clean();
        
        $results[] = new Result($contents==str_replace('<?php echo $_VIEW["page"]; ?>', "/test", file_get_contents(__DIR__."/mocks/views/404.html")), "tested exception route");
        
        $results[] = new Result(strpos(file_get_contents(dirname(__DIR__)."/errors.log"), date("Y-m-d H:i:s")." asdf")!==false, "tested reporting");
        
        return $results;
    }
}
