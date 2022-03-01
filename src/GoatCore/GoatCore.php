<?php
namespace GoatCore;

use GoatCore\Base\Config;
use GoatCore\Base\Store;

class GoatCore {

    public $store;

    /**
    * Init
    * @return void
    */
    public function __construct(Store $store) {
        $this->store = $store;
    }


    /**
    * Global configuration proxy
    * @param string|array $query
    * @return mixed
    */
    public function config($query = NULL, $data = Config::UNDEFINED_CONFIG_QUERY)
    {
        return Config::query($query, $data);
    }


    /**
    * Session proxy
    * @param string|array $query
    * @return mixed
    */
    public function session($query = NULL, $data = Config::UNDEFINED_CONFIG_QUERY)
    {
        return Config::session($query, $data);
    }
}