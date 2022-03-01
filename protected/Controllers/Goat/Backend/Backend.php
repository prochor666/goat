<?php
namespace Goat\Backend;

use GoatCore\GoatCore;

/**
* Backend - basic web controller
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Backend
{
    // Application
    protected $app;

    // Controller to run
    protected $controller;

    // Headers
    protected $headers;

    // Response
    protected $response;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->headers = [];
        $this->response = 'Backend output';
    }

    /**
    * Handle API output
    * @return object
    */
    public function setup()
    {
        $obj = ark($this->app->config('api'), $this->controller, ['class' => 'fake', 'method' => 'none']);

        if (class_exists($obj['class']) && method_exists($obj['class'], $obj['method'])) {

            $api = new $obj['class']($this->app);
            $setup = call_user_func([$api, $obj['method']]);
            $response = $setup->getData();
            $this->headers = $setup->getHeaders();
        } else {
            $this->setHeaderIfNotExist('HTTP/1.1 404 Not Found', ['HTTP/1.1', 'HTTP/1.0']);
            $response = $this->response;
        }

        $this->setResponse($response)->setAdditionalHeaders();

        return $this;
    }


    public function getOutput()
    {
        return is_string($this->response) ? $this->response['data']: dump($this->response['data']);
    }


    public function getHeaders()
    {
        return $this->headers;
    }


    protected function setHeaderIfNotExist($header, $search)
    {
        if ($this->hasHeader($search) === false) {
            array_push($this->headers, $header);
        }
    }


    protected function hasHeader($prefix = ['HTTP/1.1', 'HTTP/1.0'])
    {
        foreach($this->headers as $header) {
            if (startsWith($header, $prefix) !== false) {
                return true;
            }
        }
        return false;
    }


    /**
    * Set api response data
    * @param mixed $data
    * @return object
    */
    protected function setResponse($response)
    {
        $this->response = [
            'controller' => $this->controller,
            'data' => $response,
            'requestDuration' => requestDuration(),
        ];

        return $this;
    }


    /**
    * Set api response content data type
    * @param void
    * @return object
    */
    protected function setAdditionalHeaders()
    {
        $this->setHeaderIfNotExist('HTTP/1.1 200 OK', ['HTTP/1.1', 'HTTP/1.0']);
        $this->setHeaderIfNotExist('Expires: Thu, 19 Nov 1981 08:52:00 GMT', 'Expires:');
        $this->setHeaderIfNotExist('Cache-Control: no-store', 'Cache-Control:');
        $this->setHeaderIfNotExist('Pragma: no-cache', 'Pragma:');
        $this->setHeaderIfNotExist('Content-Type: text/html', 'Content-Type:');

        return $this;
    }
}
