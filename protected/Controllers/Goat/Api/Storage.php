<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* Storage - file storage API controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Storage implements IApiController
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
    }


    public function setup(): object
    {
        $this->model = new FileUploadModel($this->app, new DbAssets('files'));
        $route = $this->app->store->entry('GoatCore\Http\Route');

        switch (strtolower($this->method)) {

            case 'post':
                // Create directory
                $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->create($input);

                break;

            case 'put':
                // Update directory
                $id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->update($id, $input);

                break;

            case 'patch':
                // Patch directory
                $id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->patch($id, $input);

                break;

            case 'delete':
                // Patch
                $id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->delete($id, ark($input, 'soft', true));

                break;

            case 'get':
                $id = (int)$route->index($route->count() - 1);

                if ($id > 0) {

                    $this->data = $this->model->one($id);
                } else {

                    $input = !empty($_GET) ? $_GET: [];
                    $this->data = $this->model->find($input);
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
