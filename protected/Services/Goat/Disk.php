<?php
namespace Goat;

class Disk
{
    protected $config, $storageService;

    use \GoatCore\Traits\Disk;

    public function __construct($config, $storageService)
    {
        $this->config = $config;
        $this->storageService = $storageService;
    }
}
