<?php
namespace Test\Lucinda\STDERR\Application;

use Lucinda\STDERR\Application\Format;
use Lucinda\UnitTest\Result;

class FormatTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new Format(simplexml_load_string('
        <resolver format="html" content_type="text/html" class="ViewLanguageRenderer" charset="UTF-8"/>
        '));
    }

    public function getName()
    {
        return new Result($this->object->getName()=="html");
    }
        

    public function getContentType()
    {
        return new Result($this->object->getContentType()=="text/html");
    }
        

    public function getCharacterEncoding()
    {
        return new Result($this->object->getCharacterEncoding()=="UTF-8");
    }
        

    public function getViewResolver()
    {
        return new Result($this->object->getViewResolver()=="ViewLanguageRenderer");
    }
}
