<?php
namespace Lucinda\MVC\STDERR;

/**
 * Locates based on reporters tag @ XML and instances found reporters able to log error info to a storage medium.
 */
class ErrorReportersFinder
{
    private $reporters = array();

    /**
     * ErrorReportersFinder constructor.
     *
     * @param \SimpleXMLElement $xml Contents of reporters tag @ XML
     * @throws Exception If detection fails (file/class not found)
     */
    public function __construct(\SimpleXMLElement $xml) {
        $this->setReporters($xml);
    }

    /**
     * Sets found reporters based on their class name.
     *
     * @param \SimpleXMLElement $xml Contents of reporters tag @ XML
     * @throws Exception If XML contains invalid information.
     */
    private function setReporters(\SimpleXMLElement $xml) {
        $tmp = (array) $xml;
        if(empty($tmp["reporter"])) return;
        foreach($tmp["reporter"] as $info) {
            $reporterClass = (string) $info['class'];
			if(!$reporterClass) throw new Exception("Reporter tag missing class attribute");
            $this->reporters[$reporterClass] = $info;
        }
    }
    
    /**
     * Gets found reporters by their class name.
     * 
     * @return \SimpleXMLElement[string] List of error reporters by class name.
     */
    public function getReporters() {
        return $this->reporters;
    }
}

