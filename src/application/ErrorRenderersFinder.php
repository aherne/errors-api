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
     * @throws Exception If XML is misconfigured.
     */
    public function __construct(\SimpleXMLElement $xml) {
        $this->setRenderers($xml);
    }

    /**
     * Sets renderers based on content type
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @throws Exception If XML is misconfigured.
     */
    private function setRenderers(\SimpleXMLElement $xml) {
        $tmp = (array) $xml;
        if(empty($tmp["renderer"])) return;
        $list = (is_array($tmp["renderer"])?$tmp["renderer"]:[$tmp["renderer"]]);
        foreach($list as $info) {
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
     * @return ErrorRenderer[string] List of error renderers by content type.
     */
    public function getRenderers() {
        return $this->renderers;
    }
}