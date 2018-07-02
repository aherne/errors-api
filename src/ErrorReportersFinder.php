<?php
namespace Lucinda\Framework\STDERR;

require_once("ErrorReporter.php");

/**
 * Locates error reporters on disk based on XML and development environment, then instances them based on their matching XML tag.
 */
class ErrorReportersFinder
{
    private $reporters;

    /**
     * ErrorReportersFinder constructor.
     *
     * @param Application $application
     * @param string $developmentEnvironment
     * @throws Exception
     */
    public function __construct(Application $application, $developmentEnvironment) {
        $this->setReporters($application, $developmentEnvironment);
    }
    
    /**
     * Detects error reporters from XML based on development environment
     *
     * @param string $developmentEnvironment Environment application is running into (eg: live, dev, local)
     * @throws Exception
     */
    private function setReporters(Application $application, $developmentEnvironment) {
        $tmp = (array) $application->getXML()->reporters->{$developmentEnvironment};
        if(empty($tmp["reporter"])) return;
        $tmp = $tmp["reporter"];
        if(!is_array($tmp)) $tmp = array($tmp);
        foreach($tmp as $info) {
            $className = (string) $info['class'];
            $file = $application->getReportersPath()."/".$className.".php";
            if(!file_exists($file)) throw new Exception("Reporter file not found: ".$file);
            require_once($file);
            if(!class_exists($className)) throw new Exception("Reporter class not found: ".$className);
            $object = new $className($info);
            if(!($object instanceof ErrorReporter)) throw new Exception("Reporter must be instance of ErrorReporter");
            $this->reporters[] = $object;
        }
    }
    
    /**
     * Gets list of error reporters found in XML for development environment.
     * 
     * @return ErrorReporter[]
     */
    public function getReporters() {
        return $this->reporters;
    }
}

