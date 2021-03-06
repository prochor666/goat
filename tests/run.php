<?php
define('GOAT_ROOT', __DIR__ . '/..');
define('GOAT_REL', dirname($_SERVER['PHP_SELF']));

/* *******************
* Embed app core     *
* ****************** */
require_once(GOAT_ROOT.'/src/GoatCore/boot.php');

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
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'loader.php');
cliLoader($goatCore, $argv);

$handle = opendir(__DIR__);

while (false !== ($o = readdir($handle))) {

    if (( $o != '.' ) && ( $o != '..' )) {

        $fsObj = __DIR__ . DIRECTORY_SEPARATOR . $o;
        $index = $fsObj . DIRECTORY_SEPARATOR . 'index.php';

        if (is_dir($fsObj) && file_exists($index)) {

            echo "======= {$fsObj} test: ======= \n\n";
            require_once($index);
        }
    }
}

closedir($handle);