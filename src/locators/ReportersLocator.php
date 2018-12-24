<?php
namespace Lucinda\MVC\STDERR;

require_once("ClassLoader.php");
require_once("ReportersList.php");

/**
 * Locates reporters on disk based on reporters path & <reporter> tags detected beforehand,
 * then instances them
 */
class ReportersLocator {
    private $reportersList;

    /**
     * Starts detection process.
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @throws Exception If detection fails due to file/class not found.
     */
    public function __construct(Application $application) {
        $this->setReporters($application);
    }

    /**
     * Finds reporters on disk, instances them with matching <reporter> tag and appends to ReportersList
     *
     * @param Application $application Encapsulates application settings detected from xml and development environment.
     * @throws Exception If detection fails due to file/class not found.
     */
    private function setReporters(Application $application) {
		$reportersPath = $application->getReportersPath();
        $reporters = $application->getReporters();
		$temp = array();
		foreach($reporters as $className=>$xml) {
			load_class($application->getReportersPath(), $className);
			$object = new $className($xml);
			if(!($object instanceof ErrorReporter)) throw new Exception("Class must be instance of ErrorReporter");
			$temp[$className] = $object;
		}
		$this->reportersList = new ReportersList($temp);
    }

    /**
     * Gets reporters found.
     *
     * @return ReportersList
     */
    public function getReporters() {
        return $this->reportersList;
    }
}

