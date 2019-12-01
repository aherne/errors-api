<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates file format information:
 * - format: file format / name
 * - content type: content type that corresponds to above file format
 * - character encoding: charset associated to content type
 * - view resolver: (optional) view resolver class name. If not set, framework will resolve into an empty view with headers only.
 */
class Format
{
    private $name;
    private $contentType;
    private $viewResolverClass;
    private $characterEncoding;

    /**
     * Detects format info from <format> tag
     * 
     * @param \SimpleXMLElement $info
     * @throws Exception If tag is misconfigured
     */
    public function __construct(\SimpleXMLElement $info)
    {
        $this->name = (string) $info["name"];
        
        $this->contentType = (string) $info["content_type"];
        if (!$this->contentType) {
            throw new Exception("Format missing content type!");
        }
        
        $this->characterEncoding = (string) $info["charset"];
        
        $this->viewResolverClass = (string) $info['class'];
        if (!$this->viewResolverClass) {
            throw new Exception("Format missing class!");
        }
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
     * Gets view resolver class name
     *
     * @return string
     * @example JsonResolver
     */
    public function getViewResolver()
    {
        return $this->viewResolverClass;
    }
}
