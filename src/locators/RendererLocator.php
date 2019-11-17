<?php
namespace Lucinda\MVC\STDERR;

/**
 * Locates renderer on disk based on renderers path & <renderer> tag that matches response content type
 * then instances it
 */
class RendererLocator
{
    private $renderer;

    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Response $response Encapsulates response to send back to caller.
     * @param Format $detectedResponseFormat Response format detected by FrontController
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application, Response $response, Format $detectedResponseFormat)
    {
        $this->setRenderer($application, $response, $detectedResponseFormat);
    }

    /**
     * Finds renderer on disk, instances it with received parameters and saves result
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @param Response $response Encapsulates response to send back to caller.
     * @param Format $detectedResponseFormat Response format detected by FrontController
     * @throws Exception If detection fails due to file/class not found.
     */
    private function setRenderer(Application $application, Response $response, Format $detectedResponseFormat)
    {
        $rendererClass = (string) $detectedResponseFormat->getViewRenderer();
        load_class($application->getRenderersPath(), $rendererClass);
        $object = new $rendererClass();
        if (!($object instanceof ErrorRenderer)) {
            throw new Exception("Class must be instance of ErrorRenderer");
        }
        $this->renderer = $object;
    }

    /**
     * Gets renderer found.
     *
     * @return ErrorRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }
}
