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
    * https://datahub.io/core/language-codes#readme
    * JSON sample:
    * [{
    *     "name": "English",
    *     "alpha2": "en",
    *     "alpha3-b": "eng"
    * }]
    */
    public function loadAll($data, $reduce = [])
    {
        foreach ($data as $key => $lang) {

            if (!ark($lang, 'alpha2', false)) {

                unset($data[$key]);
            }
        }

        if (is_array($reduce) && count($reduce) > 0) {

            $data = $this->subset($data, $reduce);
        }

        return $data;
    }


    public function current($alpha2)
    {
        $this->lang = $alpha2;
    }


    public function t($key)
    {
        return ark($this->lang, $key, $key);
    }


    protected function subset($data, $reduce)
    {
        foreach ($data as $key => $item)
        {
            if (!in_array(ark($item, 'alpha2', '-'), $reduce)) {

                unset($data[$key]);
            }
        }

        return $data;
    }
}
