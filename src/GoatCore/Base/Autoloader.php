<?php
namespace GoatCore\Base;

/**
* GoatCore\Autoloader - class autoloader
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class Autoloader
{
    /**
    * Singleton instance
    */
    private static $instance = NULL;

    /**
    * System paths, separated by PATH_SEPARATOR
    */
    private static $registry = NULL;

    /**
    * @ignore
    */
    private function __construct(){}


    /**
    * Autoloader init, creating instance
    * @param void
    * @return object
    */
    public static function init(): object
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
    * @ignore
    */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }


    /**
    * @ignore
    */
    public function __wakeup()
    {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }


    /**
    * Register paths for require
    * @param array $param
    */
    public function register($param = []): void
    {
        spl_autoload_register([self::$instance, 'append']);
        $paths = is_array($param) ? implode(PATH_SEPARATOR, $param): $param;
        self::$registry = self::$registry.PATH_SEPARATOR.$paths;
        set_include_path(self::$registry);
    }


    /**
    * Require libs
    * @param string $className
    * @return bool
    */
    private function append($className): bool
    {
        $realPath = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        $fileSolved = stream_resolve_include_path($realPath);

        if ($fileSolved !== false) {
            require_once $fileSolved;
            return true;
        }

        return false;
    }
}
