<?php
namespace Lucinda\Framework\STDERR;

require_once("ErrorRenderer.php");

/**
 * Locates error renderer on disk based on XML, then instances it with its XML tag
 */
class ErrorRendererFinder {
    private $renderer;

    /**
     * ErrorRendererFinder constructor.
     *
     * @param Application $application
     * @param string $contentType
     * @throws Exception
     */
    public function __construct(Application $application, $contentType) {
        $this->setRenderer($application, $contentType);
    }

    /**
     * Locates renderer on dis, then instances it with its XML tag and saves result
     *
     * @param Application $application
     * @param string $contentType
     * @throws Exception
     */
    private function setRenderer($application, $contentType) {
        $tmp = (array) $application->getXML()->renderers;
        if(empty($tmp["renderer"])) return;
        $tmp = $tmp["renderer"];
        if(!is_array($tmp)) $tmp = array($tmp);
        foreach($tmp as $info) {
            $className = (string) $info['class'];
            $currentContentType = (string) $info["content_type"];
            if(!$currentContentType) throw new Exception("Renderer missing content type!");
            if($currentContentType != $contentType) continue;
            $file = $application->getRenderersPath()."/".$className.".php";
            if(!file_exists($file)) throw new Exception("Renderer file not found: ".$file);
            require_once($file);
            if(!class_exists($className)) throw new Exception("Renderer class not found: ".$className);
            $object = new $className($info);
            if(!($object instanceof ErrorRenderer)) throw new Exception("Renderer must be instance of ErrorRenderer");
            $this->renderer = $object;
        }
    }

    /**
     * Gets found error renderer.
     *
     * @return ErrorRenderer
     */
    public function getRenderer() {
        return $this->renderer;
    }
}