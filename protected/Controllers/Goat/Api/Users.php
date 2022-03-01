<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* Users - User API controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Users implements IApiController
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
        $this->model = new UsersModel($this->app, new DbAssets('users'));
        $route = $this->app->store->entry('GoatCore\Http\Route');

        switch (strtolower($this->method)) {

            case 'post':
                // Create
                $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->create($input);

                if (isset($this->data['created']) && $this->data['created']>0) {

                    $user = $this->model->one($this->data['created']);

                    /* $this->email([
                        'nameTo'    => "{$user->firstname} {$user->lastname}",
                        'mailTo'    => $user->email,
                        'mailFrom'  => $this->app->config('email')['smtp']['systemMail'],
                        'subject'   => 'New profile',
                        'body'      => 'User created with token: ' .$user->token,
                    ]); */
                }

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
                $id = (int)$route->index($route->count() - 1);
                $input = json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->delete($id, ark($input, 'soft', true));
                break;

            default:
                $id = (int)$route->index($route->count() - 1);

                if ($id > 0) {

                    $this->data = $this->model->one($id);
                } else {

                    $input = !empty($_GET) ? $_GET: [];
                    $this->data = $this->model->find($input);
                };
        }

        return $this;
    }


    private function cleanupSensitive($users): array
    {
        return array_map( function(object $user): object
        {
            unset($user->password);
            return $user;
        }, $users);
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
