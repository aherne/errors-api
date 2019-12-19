<?php
namespace Lucinda\STDERR\Response;

/**
 * Compiles criterias that will be used in generating response body
 */
class View
{
    private $file;
    private $data = [];
    
    /**
     * Sets path to template that will be the foundation of view
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }
    
    /**
     * Sets path to template that will be the foundation of view
     *
     * @param string $path
     */
    public function setFile(string $path): void
    {
        $this->file = $path;
    }
    
    /**
     * Gets path to template that will be the foundation of view
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
    
    /**
     * Gets or sets data that will be bound to template or will become the view itself.
     *
     * @param string $key
     * @param string $value
     * @return mixed|array|null
     */
    public function data(string $key="", string $value=null)
    {
        if (!$key) {
            return $this->data;
        } elseif ($value===null) {
            return (isset($this->data[$key])?$this->data[$key]:null);
        } else {
            $this->data[$key] = $value;
        }
    }
}
