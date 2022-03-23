<?php
namespace Goat;

class Template
{
    protected $config, $templateDir, $storageService, $twig;

    use \GoatCore\Traits\Disk;

    public function __construct($config, $storageService)
    {
        $this->config = $config;
        $this->storageService = $storageService;

        $this->templateDir = $this->storageService->storageDir($config['domains_id']) . DIRECTORY_SEPARATOR . 'templates';
        $this->templateCacheDir = $this->storageService->storageDir($config['domains_id']) . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR . 'cache';

        $loader = new \Twig\Loader\FilesystemLoader($this->templateDir);
        $this->twig = new \Twig\Environment($loader, [
            'cache' => $this->templateCacheDir,
        ]);
    }


    public function load($template, $data): array
    {

        return [];
    }
}
