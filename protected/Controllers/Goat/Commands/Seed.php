<?php
namespace Goat\Commands;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* Test - testing command controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Seed implements ICommandsController
{
    protected $app;

    protected $model;

    protected $input;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $db = $this->app->store->entry('GoatCore\Db\Db');
        $db->setup();
    }


    public function setup(): object
    {
        $this->model = new SeedModel($this->app, new DbAssets('users'));
        $this->input = $this->model->release();

        return $this;
    }


    public function getData()
    {
        return $this->input;
    }
}