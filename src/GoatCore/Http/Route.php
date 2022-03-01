<?php
namespace GoatCore\Http;

/**
* GoatCore\Route - simple router
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class Route
{
    private $url;

    protected $data;

    /**
    * @return void
    */
    public function __construct(Url $url)
    {
        $this->url = $url;
        $this->data = $this->url->parse()->get();
    }

    /**
    * Get url array key, seet GoatCore\Http\Url for details
    * @description full relative path
    * @return string
    */
    public function get($what = 'path')
    {
        return ark($this->data, (string)$what, false);
    }

    /**
    * Get relative path index
    * @description full relative path
    * @param integer $index
    * @return string|array
    */
    public function index($index = 0)
    {
        return ark($this->data['index'], abs($index), false);
    }

    /**
    * Get original path string
    * @description full path
    * @return string
    */
    public function path()
    {
        return $this->url->path();
    }


    /**
    * Get full path as an array
    * @description full relative path
    * @return array
    */
    public function all()
    {
        return $this->data;
    }

    /**
    * Path count
    * @description Path parts count
    * @return integer
    */
    public function count()
    {
        return count(ark($this->data, 'index', []));
    }

    /**
    * Redirect
    * @description redirect helper, utils\redirect alias
    * @param string $path = /some/path/to
    * @return void
    */
    public function redirect($path = '/')
    {
        redirect($path);
    }
}
