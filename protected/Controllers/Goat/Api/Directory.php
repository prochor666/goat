<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* Directory - file browser API controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Directory implements IApiController
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
        $db = $this->app->store->entry('GoatCore\Db\Db');
        $db->setup();
        $this->data = [];
    }


    public function setup(): object
    {
        $this->model = new DirectoryModel($this->app, new DbAssets('files'));
        $route = $this->app->store->entry('GoatCore\Http\Route');

        switch (strtolower($this->method)) {

            case 'post':

                // Create directory
                $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->create($input);
                break;

            case 'put':

                // Directory bulk action
                $domains_id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->bulkAction($domains_id, $input);
                break;

            case 'patch':

                // Rename directory
                $domains_id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->update($domains_id, $input);
                break;

            case 'delete':

                // Delete direcotry, recursive
                $domains_id = (int)$route->index($route->count() - 1);
                $input = isset($_GET) ? $_GET: [];
                $this->data = $this->model->delete($domains_id, $input);
                break;

            case 'get':

                $domains_id = (int)$route->index($route->count() - 1);
                $input = isset($_GET) ? $_GET: [];

                if ($domains_id > 0) {

                    $this->data = $this->model->list($domains_id, $input);
                }
                break;

            case 'delete':

                $this->data = [];
                break;

            default:

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
