<?php
namespace Lucinda\MVC\STDERR;

require_once("ClassLoader.php");

/**
 * Locates renderer on disk based on renderers path & <renderer> tag that matches response content type
 * then instances it
 */
class RendererLocator {
    private $renderer;

    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Response $response Encapsulates response to send back to caller.
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application, Response $response) {
        $this->setRenderer($application, $response);
    }

    /**
     * Finds renderer on disk, instances it with received parameters and saves result
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Response $response Encapsulates response to send back to caller.
     * @throws Exception If detection fails due to file/class not found.
     */
    private function setRenderer(Application $application, Response $response) {
		$renderers = $application->getRenderers();
		$contentType = $response->getHeader("Content-Type");
		if(!isset($renderers[$contentType])) throw new Exception("No renderer found for: ".$contentType);
		
        $rendererClass = (string) $renderers[$contentType]["class"];
        load_class($application->getRenderersPath(), $rendererClass);
        $object = new $rendererClass($renderers[$contentType]);
        if(!($object instanceof ErrorRenderer)) throw new Exception("Class must be instance of ErrorRenderer");
        $this->renderer = $object;
    }

    /**
     * Gets renderer found.
     *
     * @return Renderer
     */
    public function getRenderer() {
        return $this->renderer;
    }
}

