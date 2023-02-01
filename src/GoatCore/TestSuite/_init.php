<?php
/** 
 * Test embedder
 * run tests: ./vendor/bin/phpunit --verbose .\src\GoatCore\TestSuite\Tests\
 */

define('GOAT_ROOT', '.');
define('GOAT_REL', dirname($_SERVER['PHP_SELF']));

/**
 * Embed app core
 */
require_once(GOAT_ROOT.'/src/GoatCore/boot.php');

use GoatCore\Base\Autoloader;
use GoatCore\Base\Store;
use GoatCore\GoatCore;

Autoloader::init()->register([
    GOAT_ROOT . '/protected/Controllers',
    GOAT_ROOT . '/protected/Models',
    GOAT_ROOT . '/protected/Views',
    GOAT_ROOT . '/protected/Interfaces',
    GOAT_ROOT . '/protected/Traits',
    GOAT_ROOT . '/protected/Services',
]);

date_default_timezone_set('UTC');

$goatCore = new GoatCore(new Store);

$appConfigRoot = $goatCore->config('fsRoot') . '/config';
$appConfigFiles = [
    'app-config.php',
    'db-config.php',
    'mail-config.php',
];

foreach($appConfigFiles as $cf) {

    if (file_exists("{$appConfigRoot}/{$cf}")) {

        require_once("{$appConfigRoot}/{$cf}");
    }
}

$goatCore->config($config);
