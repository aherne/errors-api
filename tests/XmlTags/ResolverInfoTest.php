<?php

namespace Test\Lucinda\STDERR\XmlTags;

use Lucinda\MVC\XmlReader\Element;
use Lucinda\STDERR\XmlTags\ResolverInfo;
use Lucinda\UnitTest\Validator\Strings;

class ResolverInfoTest
{
    private ResolverInfo $object;

    public function __construct()
    {
        $this->object = new ResolverInfo(
            new Element(
                simplexml_load_string(
                    '<resolver format="txt" content_type="text/plain" charset="UTF-8" class="Test\Lucinda\STDERR\Support\PlainTextViewResolver"/>'
                )
            )
        );
    }

    public function getContentType()
    {
        return (new Strings($this->object->getContentType()))->assertEquals("text/plain");
    }

    public function getCharacterEncoding()
    {
        return (new Strings($this->object->getCharacterEncoding()))->assertEquals("UTF-8");
    }
}
