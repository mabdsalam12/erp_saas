<?php
trait Utility{
    
    private $logArray=[];
    private $timeArray=[];
    function getLog(){
        return $this->logArray;
    }
    function getRunTime(){
        return $this->timeArray;
    }

    
}