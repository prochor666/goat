<?php
use GoatCore\Base\Autoloader;
use GoatCore\Base\Store;
use GoatCore\GoatCore;

Autoloader::init()->register([
    GOAT_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'Controllers',
    GOAT_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'Models',
    GOAT_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'Views',
    GOAT_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'Interfaces',
    GOAT_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'Traits',
    GOAT_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'Services',
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
require_once(__DIR__.DIRECTORY_SEPARATOR.'loader.php');
cliLoader($goatCore, $argv);

/* ***************
| System run
*************** */
$app = new Cmd($goatCore);
$app->handle();

echo (string)$app->release();
