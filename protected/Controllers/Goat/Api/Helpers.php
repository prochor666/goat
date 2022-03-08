<?php
namespace Goat\Api;

use GoatCore\GoatCore;

/**
* Helpers - API helpers controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/


class Helpers implements IApiController
{
    protected $app;

    protected $model;

    protected $headers;

    protected $method;

    protected $data;

    public function __construct(GoatCore $app)
    {
        $this->headers = [];
        $this->app = $app;
        $this->headers = [];
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? mb_strtolower($_SERVER['REQUEST_METHOD']): 'get';
    }


    public function setup(): object
    {
        $this->model = new HelpersModel($this->app);
        $route = $this->app->store->entry('GoatCore\Http\Route');

        switch (strtolower($this->method)) {

            case 'get':
                $helper = (int)$route->index($route->count() - 1);
                $this->data = $this->model->$helper();

                break;

            default:

                $this->data = [];
        }

        return $this;
    }


    public function getHeaders(): array
    {
        return $this->headers;
    }


    public function getData()
    {
        return $this->data;
    }
}
