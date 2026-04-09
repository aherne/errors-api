<?php

namespace Test\Lucinda\STDERR\Service;

use Lucinda\MVC\XmlReader\Element;
use Lucinda\STDERR\Service\ContentTypeDetector;
use Lucinda\STDERR\XmlTags\ResolverInfo;
use Lucinda\UnitTest\Validator\Strings;

class ContentTypeDetectorTest
{
    public function getContentType()
    {
        $resolverInfo = new ResolverInfo(
            new Element(
                simplexml_load_string(
                    '<resolver format="txt" content_type="text/plain" charset="UTF-8" class="Test\Lucinda\STDERR\Support\PlainTextViewResolver"/>'
                )
            )
        );
        $object = new ContentTypeDetector($resolverInfo);

        return (new Strings($object->getContentType()))->assertEquals("text/plain; charset=UTF-8");
    }
}
