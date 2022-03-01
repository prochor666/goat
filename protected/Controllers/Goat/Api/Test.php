<?php
namespace Goat\Api;

use GoatCore\GoatCore;

/**
* Test - testing API controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Test implements IApiController
{
    protected $app;

    protected $model;

    protected $headers;

    protected $input;

    public function __construct(GoatCore $app)
    {
        $this->headers = [];
        $this->app = $app;
        $this->input = !empty($_POST) ? $_POST: json_decode( file_get_contents('php://input') );
    }


    public function setup(): object
    {
        $model = new TestModel($this->app, $this->input);
        $this->model = $model->release();
        $this->headers = [
            'Goat-test: Success'
        ];
        return $this;
    }


    public function getHeaders(): array
    {
        return $this->headers;
    }


    public function getData()
    {
        return $this->model;
    }
}