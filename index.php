<?php
define('GOAT_ROOT', __DIR__);
define('GOAT_REL', dirname($_SERVER['PHP_SELF']));

/* *******************
* Embed app core     *
* ****************** */
require_once(GOAT_ROOT . '/src/GoatCore/boot.php');


/* ********************
* Embed App          *
* ****************** */
require_once(GOAT_ROOT . '/protected/app.php');
