<?php
namespace GoatCore\Cli;

/**
* GoatCore\Command - simple commandline handler
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class ShellCommand
{
    protected $command = '';

    protected $data = [];

    /**
    * @return void
    */
    public function __construct($argv)
    {
        $this->handle($argv);
    }


    /**
    * Getter
    * @param string $property
    * @return mixed
    */
    public function get($property)
    {
        return property_exists($this, $property) ? $this->$property: false;
    }


    /**
    * Handle cmd input
    * @param array $argv
    * @return array
    */
    protected function handle($argv): array
    {
        $params = $argv;
        $forParam = '';

        foreach($params as $key => $param) {

            $parsed = $this->nameParam($param);

            if ($key === 1 && $parsed[1] === 'value') {

                $this->command = $parsed[0];
            }

            //dump("{$key} => {$parsed[0]} is {$parsed[1]}");

            if ($key > 1 && ($parsed[1] === 'input' || $parsed[1] === 'shorts')) {

                $forParam = $parsed[0];
                $this->data[$forParam] = false;

            } else if ($key > 1 && $parsed[1] === 'value') {

                $this->data[$forParam] = $parsed[0];
            }
        }

        return $this->data;
    }


    /**
    * Parse argv element
    * @param string $str
    * @return array
    */
    protected function nameParam($str): array
    {
        $types= [
            'error' => '---',
            'long' => '--',
            'short' => '-',
        ];

        if (startsWith($str, $types['error']) !== false) {

            return [false, false];
        }

        if (startsWith($str, $types['long']) !== false) {

            return [
                mb_substr($str, strlen($types['long'])),
                'input'
            ];
        }

        if (startsWith($str, $types['short']) !== false) {

            return [
                mb_substr($str, strlen($types['short'])),
                'shorts'
            ];
        }

        return [
            $str,
            'value'
        ];
    }
}
