<?php
namespace Lucinda\MVC\STDERR;

require_once("ErrorReporter.php");

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
            $className = (string) $info['class'];
            $file = $reportersPath."/".$className.".php";
            if(!file_exists($file)) throw new Exception("Reporter file not found: ".$file);
            require_once($file);
            if(!class_exists($className)) throw new Exception("Reporter class not found: ".$className);
            $object = new $className($info);
            if(!($object instanceof ErrorReporter)) throw new Exception("Reporter must be instance of ErrorReporter");
            $this->reporters[$className] = $object;
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

