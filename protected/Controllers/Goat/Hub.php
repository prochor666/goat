<?php
namespace Goat;

use GoatCore\GoatCore;
use Goat\Services\Session;
use Goat\Api\Api;
use Goat\Frontend\Frontend;
use Goat\Backend\Backend;

/**
* Hub - basic output
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Hub
{
    // Application
    protected $app;

    // Headers
    protected $headers;

    // Output
    protected $output;

    // Duration
    protected $duration;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->headers = [];
        $this->output = '';
        $this->requestDuration = requestDuration();
    }


    /**
    * Handle output, cross controller hub
    * @return void
    */
    public function handle(): void
    {
        $this->app->store->entry('Goat\Session')->start();

        $route = $this->app->store->entry('GoatCore\Http\Route');

        switch($route->index(0)) {

            case 'api':

                $controller = $route->index(1) ?? 'none';
                $contentType = $route->index(2) !== false ? $route->index(2): 'json';
                $i = new Api($this->app, $controller, $contentType);
                $obj = $i->setup();
                break;

            case 'adm':

                $i = new Backend($this->app);
                $obj = $i->setup();
                break;

            default:
                $i = new Frontend($this->app);
                $obj = $i->setup();
        }

        $this->fireEvents();
        $this->headers = $obj->getHeaders();
        $this->output = $obj->getOutput();
    }


    /**
    * Run event queue
    * @return void
    */
    protected function fireEvents(): void
    {
        $this->app->store->entry('GoatCore\Events\Queue')->setStore($this->app->store);
        $this->app->store->entry('GoatCore\Events\Queue')->fire('runtime');
    }


    /**
    * HTTP headers output
    * @return void
    */
    public function headers(): void
    {
        $this->headers = array_merge($this->headers, $this->app->config('headers'));
        $this->headers[] = "Goat-Request-Duration: {$this->requestDuration}";
        foreach ($this->headers as $header) {
            header($header, true);
        }
    }


    public function release(): string
    {
        return $this->output;
    }


    public function duration(): float
    {
        return $this->requestDuration;
    }
}
