<?php
/**
 * @doc blabla
 */
use GoatCore\Base\Autoloader;
use GoatCore\Base\Store;
use GoatCore\GoatCore;

Autoloader::init()->register(
    [
    GOAT_ROOT . '/protected/Controllers',
    GOAT_ROOT . '/protected/Models',
    GOAT_ROOT . '/protected/Views',
    GOAT_ROOT . '/protected/Interfaces',
    GOAT_ROOT . '/protected/Traits',
    GOAT_ROOT . '/protected/Services',
    ]
);

use Goat\Cmd;

date_default_timezone_set('UTC');

$goatCore = new GoatCore(new Store);

$appConfigRoot = $goatCore->config('fsRoot') . '/config';
$appConfigFiles = [
    'app-config.php',
    'db-config.php',
    'mail-config.php',
];

foreach ($appConfigFiles as $cf) {

    if (file_exists("{$appConfigRoot}/{$cf}")) {

        include_once "{$appConfigRoot}/{$cf}";
    }
}

$goatCore->config($config);

// Handle commandline options
require_once __DIR__ . '/loader.php';
cliLoader($goatCore, $argv);

/* ***************
| System run
*************** */
$app = new Cmd($goatCore);
$app->handle();

echo (string)$app->release();
