<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorReporter.php");
require_once("ClassFinder.php");

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
     * @param string $reportersPath Relative path to folder in which reporter classes are found on disk.
     * @throws Exception If detection fails (file/class not found)
     */
    public function __construct(\SimpleXMLElement $xml, $reportersPath) {
        $this->setReporters($xml, $reportersPath);
    }

    /**
     * Sets found reporters based on their class name.
     *
     * @param \SimpleXMLElement $xml Contents of reporters tag @ XML
     * @param string $reportersPath Relative path to folder in which reporter classes are found on disk.
     * @throws Exception If XML contains invalid information.
     */
    private function setReporters(\SimpleXMLElement $xml, $reportersPath) {
        $tmp = (array) $xml;
        if(empty($tmp["reporter"])) return;
        $tmp = $tmp["reporter"];
        if(!is_array($tmp)) $tmp = array($tmp);
        foreach($tmp as $info) {
            $classFinder = new ClassFinder($reportersPath, (string) $info['class']);
            $reporterClass = $classFinder->getName();

            $object = new $reporterClass($info);
            if(!($object instanceof ErrorReporter)) throw new Exception("Reporter must be instance of ErrorReporter");
            $this->reporters[$reporterClass] = $object;
        }
    }
    
    /**
     * Gets found reporters by their class name.
     * 
     * @return ErrorReporter[string] List of error reporters by class name.
     */
    public function getReporters() {
        return $this->reporters;
    }
}

