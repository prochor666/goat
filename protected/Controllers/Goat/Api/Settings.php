<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* Settings - Settings API controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Settings implements IApiController
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
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? mb_strtolower($_SERVER['REQUEST_METHOD']): 'fake';
        $db = $this->app->store->entry('GoatCore\Db\Db');
        $db->setup();
    }


    public function setup(): object
    {
        $this->model = new SettingsModel($this->app, new DbAssets('settings'));
        $route = $this->app->store->entry('GoatCore\Http\Route');

        switch (strtolower($this->method)) {

            case 'post':
            case 'put':
            case 'patch':

                $id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->update($id, $input);

                break;

            case 'get':

                $input = !empty($_GET) ? $_GET: [];
                $this->data = $this->model->find($input);

                break;

            default:

                $input = !empty($_GET) ? $_GET: [];
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
