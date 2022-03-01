<?php
namespace Goat\Commands;

use GoatCore\GoatCore;

/**
* Test - testing command controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Test implements ICommandsController
{
    protected $app;

    protected $model;

    protected $input;

    public function __construct(GoatCore $app, $input)
    {
        $this->app = $app;
        $this->input = $input;
    }


    public function setup(): object
    {
        $model = new TestModel($this->app, $this->input);
        $this->model = $model->release();
        return $this;
    }


    public function getData()
    {
        return $this->model;
    }
}