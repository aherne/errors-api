<?php
namespace Lucinda\MVC\STDERR;

/**
 * Locates <renderer> tags in XML and builds .
 */
class ErrorRenderersFinder {
    private $renderers = array();

    /**
     * ErrorRenderersFinder constructor.
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @throws Exception If XML contains invalid information.
     */
    public function __construct(\SimpleXMLElement $xml) {
        $this->setRenderers($xml);
    }

    /**
     * Sets renderers based on content type
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @throws Exception If XML contains invalid information.
     */
    private function setRenderers(\SimpleXMLElement $xml) {
        $tmp = (array) $xml;
        if(empty($tmp["renderer"])) return;
        foreach($tmp["renderer"] as $info) {
            $currentContentType = (string) $info["content_type"];
            if(!$currentContentType) throw new Exception("Renderer missing content type!");
            
            $charset = (string) $info["charset"];
            if($charset) $currentContentType .= "; charset=".$charset;
            
            $rendererClass = (string) $info['class'];
            if(!$rendererClass) throw new Exception("Renderer missing class!");
            $this->renderers[$currentContentType] = $info;
        }
    }

    /**
     * Gets found renderers
     *
     * @return \SimpleXMLElement[string] List of error renderers by content type.
     */
    public function getRenderers() {
        return $this->renderers;
    }
}