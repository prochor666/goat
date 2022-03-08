<?php
namespace Goat\Api;

use GoatCore\GoatCore;

/**
* HelpersModel - Helpers data API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class MetaModel
{
    protected $app;

    use \Goat\LangCodes;


    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->predefined = [];
    }


    public function langCodes()
    {

    }
}