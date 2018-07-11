<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorReporter.php");
require_once("ClassFinder.php");

/**
 * Locates error reporters on disk based on XML and development environment, then instances them based on their matching XML tag.
 */
class ErrorReportersFinder
{
    private $reporters = array();

    public function __construct(\SimpleXMLElement $xml, $reportersPath) {
        $this->setReporters($xml, $reportersPath);
    }
    
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
     * Gets list of error reporters found in XML for development environment.
     * 
     * @return ErrorReporter[string]
     */
    public function getReporters() {
        return $this->reporters;
    }
}

