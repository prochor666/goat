<?php
namespace Goat\Api;

use GoatCore\GoatCore;

/**
* Api - basic API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Api
{
    // Application
    protected $app;

    // Controller to run
    protected $controller;

    // Headers
    protected $headers;

    // Output data format
    protected $contentType;

    // Response
    protected $response;

    use \GoatCore\Traits\Xml;

    public function __construct(GoatCore $app, $controller, $contentType = 'json')
    {
        $this->app = $app;
        $this->controller = $controller;
        $this->headers = [];
        $this->contentType = $contentType;
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
        $obj = ark($this->app->config('api'), $this->controller, ['class' => 'fake', 'method' => 'none']);

        if (class_exists($obj['class']) && method_exists($obj['class'], $obj['method'])) {

            $api = new $obj['class']($this->app);
            $setup = call_user_func([$api, $obj['method']]);
            $response = $setup->getData();
            $this->headers = $setup->getHeaders();
        } else {

            $this->setHeaderIfNotExist('HTTP/1.1 406 Not Acceptable', ['HTTP/1.1', 'HTTP/1.0']);
            $response = $this->response;
        }

        $this->setResponse($response)->setAdditionalHeaders();

        return $this;
    }


    public function getOutput(): string
    {
        switch ($this->contentType) {
            case 'xml':
                return $this->arrayToXml($this->response);
                break;

            case 'html':
                return is_string($this->response) ? $this->response['data']: dump($this->response['data']);
                break;

            case 'text':
                return is_string($this->response) ? $this->response['data']: serialize($this->response['data']);
                break;

            default:
                return json_encode($this->response, JSON_OBJECT_AS_ARRAY);
        }
    }


    public function getHeaders(): array
    {
        return $this->headers;
    }


    protected function setHeaderIfNotExist($header, $search): void
    {
        if ($this->hasHeader($search) === false) {

            array_push($this->headers, $header);
        }
    }


    protected function hasHeader($prefix = ['HTTP/1.1', 'HTTP/1.0']): bool
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
    protected function setResponse($response): object
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
    protected function setAdditionalHeaders(): object
    {
        $this->setHeaderIfNotExist('HTTP/1.1 200 OK', ['HTTP/1.1', 'HTTP/1.0']);
        $this->setHeaderIfNotExist('Expires: Thu, 19 Nov 1981 08:52:00 GMT', 'Expires:');
        $this->setHeaderIfNotExist('Cache-Control: no-store', 'Cache-Control:');
        $this->setHeaderIfNotExist('Pragma: no-cache', 'Pragma:');
        $this->setHeaderIfNotExist('Goat-api-content-type: ' . $this->contentType, ['Goat-api-content-type:']);

        switch ($this->contentType) {
            case 'xml':
                array_push($this->headers, 'Content-Type: application/xml; charset=utf-8');
                break;

            case 'html':
                array_push($this->headers, 'Content-Type: text/html');
                break;
            case 'text':
                array_push($this->headers, 'Content-Type: text/plain');
                break;

            default:
                // default json header
                array_push($this->headers, 'Content-Type: application/json');
        }

        return $this;
    }
}
