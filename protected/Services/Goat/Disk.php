<?php
namespace Goat;

class Disk
{
    protected $config;

    use \GoatCore\Traits\Disk;

    public function __construct($config)
    {
        $this->config = $config;
    }
}
