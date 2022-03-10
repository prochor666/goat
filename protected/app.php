<?php
use GoatCore\Base\Autoloader;
use GoatCore\Base\Store;
use GoatCore\Events\Queue;
use GoatCore\Http\Route;
use GoatCore\Http\Url;
use GoatCore\Db\Db;
use GoatCore\GoatCore;

Autoloader::init()->register([
    GOAT_ROOT . '/protected/Controllers',
    GOAT_ROOT . '/protected/Models',
    GOAT_ROOT . '/protected/Views',
    GOAT_ROOT . '/protected/Interfaces',
    GOAT_ROOT . '/protected/Traits',
    GOAT_ROOT . '/protected/Services',
]);

use Goat\Hub;
use PHPMailer\PHPMailer\PHPMailer;

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

        //echo dump($appConfigRoot.DIRECTORY_SEPARATOR.$cf);
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

$goatCore->store->entry(new Queue);
$goatCore->store->entry(new Goat\Mailer(
    $goatCore->config('email'), new PHPMailer(true))
);
$goatCore->store->entry(new Url);
$goatCore->store->entry(new Route(
    $goatCore->store->entry(Url::class))
);
$goatCore->store->entry(
    new Db($goatCore->config('database'))
);
$goatCore->store->entry(
    new Goat\Storage($goatCore->config('fsRoot'))
);
$goatCore->store->entry(
    new Goat\Image($goatCore->config('image')['useImageMagick'])
);
$goatCore->store->entry(
    new Goat\Thumbnail(
        $goatCore->config('image'),
        $goatCore->store->entry('Goat\Image'),
        $goatCore->store->entry('Goat\Storage'))
);
$goatCore->store->entry(
    new Goat\Session()
);
$goatCore->store->entry(
    new Goat\Lang()
);

/* ***************
| Site run
*************** */
$app = new Hub($goatCore);
$app->handle();
$app->headers();
echo (string)$app->release();

ob_end_flush();

echo $buffer;