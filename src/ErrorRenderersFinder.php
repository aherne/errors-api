<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorRenderer.php");
require_once("ClassLoader.php");

/**
 * Locates based on renderers tag @ XML and instances found renderers able to give a response back to caller after an error
 * fed STDERR flow.
 */
class ErrorRenderersFinder {
    private $renderers = array();

    /**
     * ErrorRenderersFinder constructor.
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @param string $renderersPath Relative path to folder in which renderer classes are found on disk.
     * @throws Exception If XML contains invalid information.
     */
    public function __construct(\SimpleXMLElement $xml, $renderersPath) {
        $this->setRenderer($xml, $renderersPath);
    }

    /**
     * Sets renderers based on content type
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @param string $renderersPath Relative path to folder in which renderer classes are found on disk.
     * @throws Exception If XML contains invalid information.
     */
    private function setRenderer(\SimpleXMLElement $xml, $renderersPath) {
        $tmp = (array) $xml;
        if(empty($tmp["renderer"])) return;
        $tmp = $tmp["renderer"];
        if(!is_array($tmp)) $tmp = array($tmp);
        foreach($tmp as $info) {
            $currentContentType = (string) $info["content_type"];
            if(!$currentContentType) throw new Exception("Renderer missing content type!");
            
            $rendererClass = (string) $info['class'];
            load_class($renderersPath, $rendererClass);
            $object = new $rendererClass($info);
            if(!($object instanceof ErrorRenderer)) throw new Exception("Renderer must be instance of ErrorRenderer");
            $this->renderers[$currentContentType] = $object;
        }
    }

    /**
     * Gets found renderers
     *
     * @return ErrorRenderer[string] List of error renderers by content type.
     */
    public function getRenderers() {
        return $this->renderers;
    }
}