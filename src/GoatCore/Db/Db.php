<?php
namespace GoatCore\Db;

use \RedBeanPHP\R;

class Db implements \GoatCore\Interfaces\IDb
{
    protected $driver;

    protected $config = [
        'engine'    => 'mysql',
        'host'      => '127.0.0.1',
        'name'      => 'dbname',
        'username'  => 'root',
        'password'  => '',
        'port'      => '3307',
        'prefix'    => '',
    ];

    public function __construct($config)
    {
        $this->config = $config;
    }


    public function setup(): object
    {
        return $this->compose();
    }


    public function add($name, $config): void
    {
        $dsn = "{$config['engine']}:host={$config['host']};port={$config['port']};dbname={$config['name']}";
        R::addDatabase($name, $dsn, $config['username'], $config['password']);
    }


    public function select($name): void
    {
        R::selectDatabase($name);
    }


    public function default(): void
    {
        R::selectDatabase('default');
    }


    protected function compose(): object
    {
        $defaults = [
            'engine'    => 'mysql',
            'host'      => '127.0.0.1',
            'name'      => 'warp',
            'username'  => 'root',
            'password'  => '',
            'port'      =>  '3306',
            'prefix'    => 'warp',
        ];

        $this->config = array_merge($defaults, $this->config);

        $dsn = "{$this->config['engine']}:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['name']}";

        return R::setup($dsn, $this->config['username'], $this->config['password']);
        R::debug(true, 2);
    }
}
