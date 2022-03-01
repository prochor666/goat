<?php
use GoatCore\Base\Autoloader;
use GoatCore\Base\Store;
use GoatCore\Cli\ShellCommand;
use GoatCore\Db\Db;
use GoatCore\GoatCore;
use GoatCore\Images\ImageMagick;

Autoloader::init()->register([
    GOAT_ROOT . '/protected/Controllers',
    GOAT_ROOT . '/protected/Models',
    GOAT_ROOT . '/protected/Views',
    GOAT_ROOT . '/protected/Interfaces',
    GOAT_ROOT . '/protected/Traits',
    GOAT_ROOT . '/protected/Services',
]);

use Goat\Cmd;

date_default_timezone_set('UTC');

$goatCore = new GoatCore(new Store);

$appConfigRoot = $goatCore->config('fsRoot').DIRECTORY_SEPARATOR.'config';
$appConfigFiles = [
    'app-config.php',
    'db-config.php',
    'mail-config.php',
];

foreach($appConfigFiles as $cf) {

    if (file_exists($appConfigRoot.DIRECTORY_SEPARATOR.$cf)) {

        require_once($appConfigRoot.DIRECTORY_SEPARATOR.$cf);
    }
}

$goatCore->config($config);

// Handle commandline options
$command = new ShellCommand($argv);
$goatCore->store->entry($command);
$goatCore->store->entry(new Db($goatCore->config('database')));
$goatCore->store->entry(new ImageMagick());

/* ***************
| Site run
*************** */
$app = new Cmd($goatCore);
$app->handle();

echo (string)$app->release();
