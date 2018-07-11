<?php
namespace Lucinda\MVC\STDERR;

class ReportersList
{
    private $reporters = array();
    
    public function __construct($reporters) {
        $this->reporters = $reporters;
    }
    
    public function clear() {
        $this->reporters = array();
    }
    
    public function disable($reporterClassName) {
        unset($this->reporters[$reporterClassName]);
    }
    
    public function toArray() {
        return $this->reporters;
    }
}

