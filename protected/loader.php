<?php
function webLoader($goatCore): void
{
    $goatCore->store->entry(new \GoatCore\Events\Queue);
    $goatCore->store->entry(new \Goat\Mailer(
        $goatCore->config('email'), new \PHPMailer\PHPMailer\PHPMailer(true))
    );
    $goatCore->store->entry(new \GoatCore\Http\Url);
    $goatCore->store->entry(new \GoatCore\Http\Route(
        $goatCore->store->entry(\GoatCore\Http\Url::class))
    );
    $goatCore->store->entry(
        new \GoatCore\Db\Db($goatCore->config('database'))
    );
    $goatCore->store->entry(
        new \Goat\Storage($goatCore->config('fsRoot'))
    );
    $goatCore->store->entry(
        new \Goat\Image($goatCore->config('image')['useImageMagick'])
    );
    $goatCore->store->entry(
        new \Goat\Thumbnail(
            $goatCore->config('image'),
            $goatCore->store->entry('Goat\Image'),
            $goatCore->store->entry('Goat\Storage'))
    );
    $goatCore->store->entry(
        new \Goat\Session()
    );
    $goatCore->store->entry(
        new \Goat\Lang()
    );
    $goatCore->store->entry(
        new \Goat\Disk()
    );
    $goatCore->store->entry(
        new \Goat\Template($goatCore->store->entry('Goat\Storage'))
    );
}


function cliLoader($goatCore, $argv): void
{
    $command = new GoatCore\Cli\ShellCommand($argv);
    $goatCore->store->entry($command);
    $goatCore->store->entry(new GoatCore\Db\Db($goatCore->config('database')));
    $goatCore->store->entry(
        new Goat\Image($goatCore->config('image')['useImageMagick'])
    );
}