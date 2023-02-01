<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
 * Navs - Navs API controller
 *
 * @author  Jan Prochazka aka prochor <prochor666@gmail.com>
 * @version 1.0
 */

class Navs implements IApiController
{
    protected $app;

    protected $model;

    protected $headers;

    protected $method;

    protected $data;

    protected $validatorService;

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
        $this->model = new NavsModel($this->app, new DbAssets('navs'));
        $route = $this->app->store->entry('GoatCore\Http\Route');
        $this->validatorService = $this->app->store->entry('Goat\Validator');

        switch (strtolower($this->method)) {

        case 'post':
            // Create
            $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
            $this->data = $this->model->create($input);
            break;

        case 'put':
            // Update full dataset
            $id = (int)$route->index($route->count() - 1);
            $input = json_decode(file_get_contents('php://input'), true);
            $this->data = $this->model->update($id, $input);
            break;

        case 'patch':
            // Update partial
            $id = (int)$route->index($route->count() - 1);
            $input = json_decode(file_get_contents('php://input'), true);
            $this->data = $this->model->patch($id, $input);
            break;

        case 'delete':
            // Remove nav
            $id = (int)$route->index($route->count() - 1);
            $input = json_decode(file_get_contents('php://input'), true);
            $this->data = $this->model->delete($id, ark($input, 'soft', true));
            break;

        default:
            $id = (int)$route->index($route->count() - 1);

            if ($route->count() === 3) {

                $this->data = $this->model->one((int)$id);
                $this->data = $this->model->metaService->attach($this->data, 'navs');

            } else {

                $input = !empty($_GET) ? $_GET: [];
                $this->data = $this->model->findRelated($input);
            }
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
