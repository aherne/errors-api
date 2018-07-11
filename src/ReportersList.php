<?php
namespace Lucinda\MVC\STDERR;

/**
 * Encapsulates list of reporters detected from XML and instanced by ErrorReportersFinder controllers may later on clear
 * or disable if reporting policies are exception-based instead of global.
 */
class ReportersList
{
    private $reporters = array();

    /**
     * ReportersList constructor.
     * @param ErrorReporter[string] $reporters List of detected ErrorReporter instances indexed by their class name.
     */
    public function __construct($reporters) {
        $this->reporters = $reporters;
    }

    /**
     * Clears all reporters
     */
    public function clear() {
        $this->reporters = array();
    }

    /*
     * Disables a particular reporter by its class name.
     *
     * @param string $reporterClassName
     */
    public function disable($reporterClassName) {
        unset($this->reporters[$reporterClassName]);
    }

    /**
     * Outputs reporters to array
     *
     * @return ErrorReporter[string] $reporters List of remaining ErrorReporter instances indexed by their class name.
     */
    public function toArray() {
        return $this->reporters;
    }
}

