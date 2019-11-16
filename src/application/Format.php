<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates file format information:
 * - format: file format / name
 * - content type: content type that corresponds to above file format
 * - character encoding: charset associated to content type
 * - view resolver: (optional) view resolver class name. If not set, framework will resolve into an empty view with headers only.
 * Utility @ Application class.
 *
 * @author aherne
 */
class Format
{
    private $name;
    private $contentType;
    private $viewRendererClass;
    private $characterEncoding;

    /**
     * Saves response format data detected from XML tag "renderer".
     *
     * @param string $name
     * @param string $contentType
     * @param string $characterEncoding
     * @param string $viewRendererClass
     */
    public function __construct($name, $contentType, $characterEncoding="", $viewRendererClass="")
    {
        $this->name = $name;
        $this->contentType = $contentType;
        $this->characterEncoding= $characterEncoding;
        $this->viewRendererClass = $viewRendererClass;
    }

    /**
     * Gets response format name (extension).
     *
     * @return string
     * @example json
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets content type
     *
     * @return string
     * @example application/json
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    
    /**
     * Gets character encoding (charset)
     *
     * @return string
     */
    public function getCharacterEncoding()
    {
        return $this->characterEncoding;
    }

    /**
     * Gets view renderer class name
     *
     * @return string
     * @example JsonRenderer
     */
    public function getViewRenderer()
    {
        return $this->viewRendererClass;
    }
}
