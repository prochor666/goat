<?php
namespace Goat;


/*
* Loads json with lang codec
* Source:
* https://datahub.io/core/language-codes#readme
*/
trait LangCodes
{
    use \GoatCore\Traits\Disk;

    public function load($langFile)
    {
        if ($this->isFile($langFile)) {

            return json_decode($this->readFile($langFile), true);
        } else {

            return [
                [
                    'name'     =>  'English',
                    'alpha2'   => 'en',
                    'alpha3-b' => 'eng'
                ],
            ];
        }
    }
}
