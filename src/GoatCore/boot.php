<?php
// MAIN CONFIGURATION
if (version_compare(PHP_VERSION, '7.2.5', '<')) {

    die('Now runing '.PHP_VERSION.'. You need PHP version 7.2.5 or later.');
}

mb_internal_encoding('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');


/* ****************************************
*  Load Black magic                       *
* *************************************** */
use GoatCore\Base\Config;
use GoatCore\Base\Autoloader;
use GoatCore\Errors\ErrorHandler;

require_once GOAT_ROOT . '/src/GoatCore/Base/Autoloader.php';
require_once GOAT_ROOT . '/src/GoatCore/utils.php';
require_once GOAT_ROOT . '/src/GoatCore/format.php';
require_once GOAT_ROOT . '/vendor/autoload.php';

try {
    // Lets make some autoloading
    Autoloader::init()->register([
        GOAT_ROOT . '/src',
        GOAT_ROOT . '/src/Traits',
    ]);

    if (file_exists(GOAT_ROOT . '/.dots/.reporterrors')) {

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $err = new ErrorHandler;
        set_error_handler([$err, 'handleError'], E_ALL);
        set_exception_handler([$err, 'handleException']);
        register_shutdown_function([$err, 'handleFatalError']);
    } else {

        ini_set('display_errors', 0);
        error_reporting(0);
    }

    Config::init();

}catch (Exception $e) {

    die('Fix boot: ' . $e->getMessage());
}
