<?php
namespace GoatCore\Logger;

/**
* GoatCore\Logger - simple log system
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class Logger
{
    protected $logStorage;

    /**
    * @ignore
    */
    public function __construct($logStorage)
    {
        $this->logStorage = $logStorage;
    }


    /**
    * log message
    * @description Save log message
    * @param string $message
    * @return void
    */
    public function save($message): void
    {
        $this->logStorage->save($message);
    }
}
