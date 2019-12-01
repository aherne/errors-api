<?php
namespace Lucinda\STDERR;


/**
 * Defines blueprint for reporting an exception that fed STDERR flow to a storage medium (eg: log file)
 */
abstract class Reporter implements Runnable
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;
    
    /**
     * Reports error info to a storage medium.
     *
     * @param Request $request Encapsulates error request, including exception/error itself and route that maps it.
     * @param \SimpleXMLElement $xml XML that sets up individual reporter
     */
    public function __construct(Request $request, \SimpleXMLElement $xml): void
    {
        $this->request = $request;
        $this->xml = $xml;
    }
}
