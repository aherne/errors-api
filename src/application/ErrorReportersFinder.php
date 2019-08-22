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
     * @throws Exception If XML is misconfigured.
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        $this->setReporters($xml);
    }

    /**
     * Sets found reporters based on their class name.
     *
     * @param \SimpleXMLElement $xml Contents of reporters tag @ XML
     * @throws Exception If XML is misconfigured.
     */
    private function setReporters(\SimpleXMLElement $xml)
    {
        $tmp = (array) $xml;
        if (empty($tmp["reporter"])) {
            return;
        }
        $list = (is_array($tmp["reporter"])?$tmp["reporter"]:[$tmp["reporter"]]);
        foreach ($list as $info) {
            $reporterClass = (string) $info['class'];
            if (!$reporterClass) {
                throw new Exception("Reporter tag missing class attribute");
            }
            $this->reporters[$reporterClass] = $info;
        }
    }
    
    /**
     * Gets found reporters by their class name.
     *
     * @return ErrorReporter[string] List of error reporters by class name.
     */
    public function getReporters()
    {
        return $this->reporters;
    }
}
