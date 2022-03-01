<?php
namespace Goat;

use GoatCore\GoatCore;
use Goat\Commands\Command;

/**
* Cmd - basic shell output
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Cmd
{
    // Application
    protected $app;

    // Output
    protected $output;

    // Duration
    protected $duration;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->output = '';
        $this->requestDuration = requestDuration();
    }


    /**
    * Handle output, cross controller hub
    * @return void
    */
    public function handle(): void
    {
        $command = $this->app->store->entry('GoatCore\Cli\ShellCommand');
        $controller = $command->get('command') ?? 'none';
        $i = new Command($this->app, $controller, $command->get('data'));
        $obj = $i->setup();

        $this->output = $obj->getOutput();
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
