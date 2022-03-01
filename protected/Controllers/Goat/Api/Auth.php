<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* Auth - authorization API controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class Auth implements IApiController
{
    protected $app;

    protected $model;

    protected $headers;

    protected $method;

    protected $data;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->headers = [];
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? mb_strtolower($_SERVER['REQUEST_METHOD']): 'get';
        $db = $this->app->store->entry('GoatCore\Db\Db');
        $db->setup();
    }


    public function setup(): object
    {
        $this->model = new AuthModel($this->app, new DbAssets('users'));

        switch (strtolower($this->method)) {

            case 'post':
                // Login
                $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->login($input);

                if (isset($this->data['logged']) && $this->data['logged'] === true) {

                    $user = $this->data['user'];

                    $q = $this->app->store->entry('GoatCore\Events\Queue');

                    $ev = $q->add([
                        'class'     => 'Goat\Mailer',
                        'method'    => 'send',
                        'data'      => [
                            'nameTo'    => "{$user->firstname} {$user->lastname}",
                            'mailTo'    => $user->email,
                            'mailFrom'  => $this->app->config('email')['smtp']['systemMail'],
                            'subject'   => 'New login',
                            'body'      => "User {$user->username} login checkj, IP: " . clientIp(),
                        ],
                    ]);

                    $this->data['registered_events'] = $q->list();
                    $this->data['event_containers'] = $q->listContainers();
                }

                break;

            case 'put':
                // Logout all sessions
                $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->logoutGlobal();
                break;

            case 'patch':
                // Activate user
                $input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->activate($input);
                break;

            case 'delete':
                // Logout current session
                //$input = !empty($_POST) ? $_POST: json_decode(file_get_contents('php://input'), true);
                $this->data = $this->model->logout();
                break;

            default:

                $input = !empty($_GET) ? $_GET: [];
                $this->data = $this->model->logged();
        }

        //$this->setUserSession();

        return $this;
    }


    protected function setUserSession()
    {
        $logged = ark($this->data, 'logged', false);

        if ($logged === true) {

            $this->app->session('user', $this->data['user']);
        } else {

            $this->app->session('user', []);
        }
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
