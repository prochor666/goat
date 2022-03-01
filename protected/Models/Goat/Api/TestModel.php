<?php
namespace Goat\Api;

use GoatCore\GoatCore;

/**
* Test - testing API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class TestModel
{
    protected $app;

    protected $input;

    protected $data;

    public function __construct(GoatCore $app, $input = [])
    {
        $this->app = $app;
        $this->input = $input;
    }


    public function release(): array
    {
        $obj = new \StdClass;
        $obj->testClassPropertyArray = [
            'x' => 234,
        ];
        $obj->testClassPropertyString = '<div class="info">HTML snippet & \'special\' "chars"</div>';
        $obj->testClassPropertyExplanationString = 'Classes are automatically converted into the arrays';

        $this->data = [
            'status' => true,
            'PHP' => phpversion(),
            'reason' => 'Test model call success',
            'requestMethod' => $_SERVER['REQUEST_METHOD'],
            'data' => [
                'string' => 'ABCD',
                'int' => 100,
                'float' => 3.14,
                'bool' => true,
                'nonAssociativeArray' => ['A','B','C'],
                'StdClass' => $obj,
                'sessionSaveHandler' => ini_get('session.save_handler'),
            ],
            'input' => [
                $_SERVER['REQUEST_METHOD'] => $this->input
            ],
        ];

        if ($this->data['requestMethod'] !== 'GET') {

            $this->data['input'][$this->data['requestMethod']] = $this->input;
        }

        return $this->data;
    }
}