<?php
namespace Goat\Commands;

use GoatCore\GoatCore;

/**
* Test - testing command model
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


    public function release()
    {
        $this->data = $this->input;
        return $this->data;
    }
}