<?php
namespace GoatCore\Http;

/**
* GoatCore\Url - simple url parser/extractor
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class Url
{
    protected $originPath;

    protected $data;

    /**
    * @ignore
    */
    public function __construct($path = NULL)
    {
        if (is_null($path) && isset($_SERVER) && ark($_SERVER, 'HTTP_HOST', false) !== false && ark($_SERVER, 'REQUEST_URI', false) !== false) {
            $this->originPath = (ssl() ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        } else {
            $this->originPath = (string)$path;
        }
    }


    /**
    * Url parse
    * @description Create complex array from given path
    * @param void
    * @return array
    */
    public function parse()
    {
        $parsed_url = parse_url($this->originPath);

        $this->data = [
            'scheme'   => isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '',
            'host'     => isset($parsed_url['host']) ? $parsed_url['host'] : '',
            'port'     => isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '',
            'user'     => isset($parsed_url['user']) ? $parsed_url['user'] : '',
            'pass'     => isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '',
            'path'     => isset($parsed_url['path']) ? $parsed_url['path'] : '',
            'queryString'    => isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '',
            'fragment' => isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '',
            'index'    => [],
            'query' => [],
        ];

        if (isset($parsed_url['query']) && mb_strlen($parsed_url['query'])>0) {
            parse_str($parsed_url['query'], $this->data['query']);
        }

        $this->data['index'] = array_values(array_filter(explode('/', $this->data['path']), function($element){
            return mb_strlen($element)>0 ? true: false;
        }));

        return $this;
    }


    /**
    * Get path
    * @description Get original path string
    * @return string
    */
    public function path()
    {
        return $this->originPath;
    }


    /**
    * Get data
    * @description Get parsed array
    * @return array
    */
    public function get()
    {
        return $this->data;
    }

}
