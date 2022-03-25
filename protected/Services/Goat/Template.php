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
    }


    public function load($domains_id, $template, $data): array
    {
        $this->templateDir = $this->storageService->domainStorageDir($config['domains_id']) . DIRECTORY_SEPARATOR . 'templates';
        $this->templateCacheDir = $this->storageService->domainStorageDir($config['domains_id']) . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR . 'cache';

        $loader = new \Twig\Loader\FilesystemLoader($this->templateDir);
        $this->twig = new \Twig\Environment($loader, [
            'cache' => $this->templateCacheDir,
        ]);

        return [];
    }
}
