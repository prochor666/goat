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

use Goat\Hub;

date_default_timezone_set('UTC');

mb_http_output("UTF-8");
ob_start("mb_output_handler");
$buffer = ob_get_contents();

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

session_set_cookie_params([
    'lifetime' => $goatCore->config('session')['cache_expire'],
    'path' => '/',
    //'domain' => $_SERVER['HTTP_HOST'],
    'secure' => $goatCore->config('session')['cookie_secure'],
    'httponly' => $goatCore->config('session')['cookie_httponly'],
    'samesite' => $goatCore->config('session')['cookie_samesite'],
]);

// Load web services
require_once(__DIR__.DIRECTORY_SEPARATOR.'loader.php');
webLoader($goatCore);

/* ***************
| Site run
*************** */
$app = new Hub($goatCore);
$app->handle();
$app->headers();
echo (string)$app->release();

ob_end_flush();

echo $buffer;