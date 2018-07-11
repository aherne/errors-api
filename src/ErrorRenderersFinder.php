<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorRenderer.php");
require_once("ClassFinder.php");

/**
 * Locates error renderer on disk based on XML, then instances it with its XML tag
 */
class ErrorRenderersFinder {
    private $renderers = array();

    public function __construct(\SimpleXMLElement $xml, $renderersPath) {
        $this->setRenderer($xml, $renderersPath);
    }

    private function setRenderer(\SimpleXMLElement $xml, $renderersPath) {
        $tmp = (array) $xml;
        if(empty($tmp["renderer"])) return;
        $tmp = $tmp["renderer"];
        if(!is_array($tmp)) $tmp = array($tmp);
        foreach($tmp as $info) {
            $currentContentType = (string) $info["content_type"];
            if(!$currentContentType) throw new Exception("Renderer missing content type!");

            $classFinder = new ClassFinder($renderersPath, (string) $info['class']);
            $rendererClass = $classFinder->getName();

            $object = new $rendererClass($info);
            if(!($object instanceof ErrorRenderer)) throw new Exception("Renderer must be instance of ErrorRenderer");
            $this->renderers[$currentContentType] = $object;
        }
    }

    public function getRenderers() {
        return $this->renderers;
    }
}