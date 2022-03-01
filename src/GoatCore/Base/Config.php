<?php
namespace GoatCore\Base;

use GoatCore\Base\Disk;

/**
* Base config class, initalizes config and registry
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class Config
{
    const UNDEFINED_CONFIG_QUERY = 'WARP_UNDEFINED_CONFIG_QUERY';

    /**
    * @ignore
    */
    private static $instance = NULL;

    /**
    * @ignore
    */
    private static $defaults = false;


    /**
    * GoatCore class contructor
    * @param void
    * @return void
    */
    public function __construct()
    {
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
    * Config init, creating registry, use it once at boot
    * @param void
    * @return object
    */
    public static function init(): object
    {
        if (self::$instance === NULL)
        {
            self::$instance = new self();
            self::setDefaults();
        }

        return self::$instance;
    }


    /**
    * Global configuration
    * @param string|array $query
    * @return mixed
    */
    public static function query($query = NULL, $data = self::UNDEFINED_CONFIG_QUERY)
    {
        if (is_null($query)) {

            return self::$defaults;
        }

        if (is_array($query)) {

            $query = array_filter($query, ['self', 'allowKeyChange'], ARRAY_FILTER_USE_KEY);
            self::$defaults = array_merge_recursive(self::$defaults, $query);
            return self::$defaults;
        }

        if (is_string($query) && $data !== self::UNDEFINED_CONFIG_QUERY && self::allowKeyChange($query)) {

            self::$defaults[$query] = $data;
        }

        if (is_string($query) && $data !== self::UNDEFINED_CONFIG_QUERY && !self::allowKeyChange($query)) {

            throw new \LogicException("Protected configuration option {$query} can not be modified.");
        }

        if (!is_string($query) && !is_array($query)) {

            $type = gettype($query);
            throw new \LogicException("The configuration option must be an array type or a string, {$type} given");
        }

        return ark(self::$defaults, (string)$query, self::UNDEFINED_CONFIG_QUERY);
    }


    /**
    * SESSION based registry
    * @param string|array $query
    * @return mixed
    */
    public static function session($query, $data = self::UNDEFINED_CONFIG_QUERY)
    {
        $result = null;

        if (isset($_SESSION)) {

            if ($data !== self::UNDEFINED_CONFIG_QUERY) {

                $_SESSION[(string)$query] = $data;
            }
            $result = ark($_SESSION, (string)$query, self::UNDEFINED_CONFIG_QUERY);
        } else {

            $result = $data;
        }

        return $result;
    }


    /**
    * Config init, creating registry, use it once at boot
    * @param void
    * @return void
    */
    private static function setDefaults(): void
    {
        self::$defaults = !is_array(self::$defaults) ? [
            'fsRoot'             => GOAT_ROOT,
            'httpclientIp'       => clientIp() ?? null,
            'httpHost'           => $_SERVER["HTTP_HOST"] ?? null,
            'httpRequest'        => $_SERVER["REQUEST_URI"] ?? null,
            'httpPort'           => $_SERVER["SERVER_PORT"] ?? null,
            'httpPath'           => $_SERVER["PATH_INFO"] ?? null,
            'httpScriptDir'      => dirname($_SERVER['SCRIPT_NAME']) ?? null,
            'httpSSL'            => ssl(),
            'httpScheme'         => ssl() === true ? 'https://': 'http://',
            'httpForceSSL'       => false,
            'netServerIP'        => $_SERVER["SERVER_ADDR"] ?? null,
            'gatewayInterface'   => $_SERVER["GATEWAY_INTERFACE"] ?? null,
            'serverSoftware'     => $_SERVER["SERVER_SOFTWARE"] ?? null,
            'sapi'               => PHP_SAPI,
        ]: self::$defaults;
    }


    /**
    * Protect some values
    * @param string
    * @return bool
    */
    private static function allowKeyChange($key): bool
    {
        $protected  = [
            'fsRoot'            => false,
            'httpHost'          => false,
            'httpRequest'       => false,
            'httpPort'          => false,
            'httpPath'          => false,
            'httpScriptDir'     => false,
            'httpSSL'           => false,
            'httpScheme'        => false,
            'httpForceSSL'      => true,
            'netServerIP'       => false,
            'gatewayInterface'  => false,
            'sapi'              => false,
        ];

        return ark($protected, $key, true);
    }
}
