<?php
namespace Goat\Commands;

use GoatCore\GoatCore;

/**
* Command - basic Command model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Command
{
    // Application
    protected $app;

    // Controller to run
    protected $controller;

    // Iput data
    protected $input;

    // Response
    protected $response;

    use \GoatCore\Traits\Xml;

    public function __construct(GoatCore $app, $controller, $input)
    {
        $this->app = $app;
        $this->controller = $controller;
        $this->input = $input;
        $this->response = [
            "error" => "Error, no controller '{$this->controller}' found"
        ];
    }


    /**
    * Handle API output
    * @return object
    */
    public function setup(): object
    {
        $obj = ark($this->app->config('commands'), $this->controller, ['class' => 'fake', 'method' => 'none']);

        if (class_exists($obj['class']) && method_exists($obj['class'], $obj['method'])) {

            $api = new $obj['class']($this->app, $this->input);
            $setup = call_user_func([$api, $obj['method']]);
            $response = $setup->getData();
        } else {

            $response = $this->response;
        }

        $this->setResponse($response);

        return $this;
    }


    public function getOutput(): string
    {
        return json_encode($this->response, JSON_PRETTY_PRINT);
    }


    /**
    * Set api response data
    * @param mixed $data
    * @return object
    */
    protected function setResponse($response): object
    {
        $this->response = [
            'controller' => $this->controller,
            'data' => $response,
            'requestDuration' => requestDuration(),
        ];

        return $this;
    }
}
