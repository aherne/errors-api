<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorRenderer.php");

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
            $className = (string) $info['class'];
            $currentContentType = (string) $info["content_type"];
            if(!$currentContentType) throw new Exception("Renderer missing content type!");
            $file = $renderersPath."/".$className.".php";
            if(!file_exists($file)) throw new Exception("Renderer file not found: ".$file);
            require_once($file);
            if(!class_exists($className)) throw new Exception("Renderer class not found: ".$className);
            $object = new $className($info);
            if(!($object instanceof ErrorRenderer)) throw new Exception("Renderer must be instance of ErrorRenderer");
            $this->renderers[$currentContentType] = $object;
        }
    }

    public function getRenderers() {
        return $this->renderers;
    }
}