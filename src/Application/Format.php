<?php
namespace Lucinda\STDERR\Application;

use Lucinda\STDERR\Exception;

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
     * Detects format info from <resolver> tag
     *
     * @param \SimpleXMLElement $info
     * @throws Exception If tag is misconfigured
     */
    public function __construct(\SimpleXMLElement $info)
    {
        $this->name = (string) $info["format"];
        
        $this->contentType = (string) $info["content_type"];
        if (!$this->contentType) {
            throw new Exception("Attribute 'content_type' is mandatory for 'resolver' tag");
        }
        
        $this->characterEncoding = (string) $info["charset"];
        
        $this->viewResolverClass = (string) $info['class'];
        if (!$this->viewResolverClass) {
            throw new Exception("Attribute 'class' is mandatory for 'resolver' tag");
        }
    }

    /**
     * Gets response format name (extension).
     *
     * @return string
     * @example json
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets content type
     *
     * @return string
     * @example application/json
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }
    
    /**
     * Gets character encoding (charset)
     *
     * @return string
     */
    public function getCharacterEncoding(): string
    {
        return $this->characterEncoding;
    }

    /**
     * Gets view resolver class name
     *
     * @return string
     * @example JsonResolver
     */
    public function getViewResolver(): string
    {
        return $this->viewResolverClass;
    }
}
