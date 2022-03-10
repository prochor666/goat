<?php
namespace Goat;


class Lang
{
    protected $lang;

    public function __construct()
    {
        $this->lang = 'en';
    }


    /**
    * Configure lang alpha2 code values
    * ISO-639 array is required
    * JSON sample:
    * [{
    *     "name": "English",
    *     "alpha2": "en",
    *     "alpha3-b": "eng"
    * }]
    */
    public function loadAll($config)
    {
        foreach ($config as $key => $lang) {

            if (!ark($lang, 'alpha2', false)) {

                unset($config[$key]);
            }
        }

        return $config;
    }


    public function current($alpha2)
    {
        $this->lang = $alpha2;
    }


    public function t($key)
    {
        return ark($this->lang, $key, $key);
    }
}
