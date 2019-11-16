<?php
namespace Lucinda\MVC\STDERR;

require_once("Format.php");

/**
 * Locates <renderer> tags in XML and builds .
 */
class ErrorRenderersFinder
{
    private $renderers = array();

    /**
     * ErrorRenderersFinder constructor.
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @throws Exception If XML is misconfigured.
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        $this->setRenderers($xml);
    }

    /**
     * Sets renderers based on content type
     *
     * @param \SimpleXMLElement $xml Contents of renderers tag @ XML
     * @throws Exception If XML is misconfigured.
     */
    private function setRenderers(\SimpleXMLElement $xml)
    {
        $tmp = (array) $xml;
        if (empty($tmp["renderer"])) {
            return;
        }
        $list = (is_array($tmp["renderer"])?$tmp["renderer"]:[$tmp["renderer"]]);
        foreach ($list as $info) {
            $displayFormat = (string) $info["format"];
            if (!$displayFormat) {
                throw new Exception("Renderer missing display format!");
            }
            
            $contentType = (string) $info["content_type"];
            if (!$contentType) {
                throw new Exception("Renderer missing content type!");
            }
            
            $charset = (string) $info["charset"];
            
            $rendererClass = (string) $info['class'];
            if (!$rendererClass) {
                throw new Exception("Renderer missing class!");
            }
            $this->renderers[$displayFormat] = new Format($displayFormat, $contentType, $charset, $rendererClass);
        }
    }

    /**
     * Gets found renderers
     *
     * @return Format[string] List of error renderers by content type.
     */
    public function getRenderers()
    {
        return $this->renderers;
    }
}
