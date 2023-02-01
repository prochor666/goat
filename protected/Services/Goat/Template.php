<?php
namespace Goat;

use Goat\Storage;

class Template
{
    protected $storageService;

    public function __construct(Storage $storageService)
    {
        $this->storageService = $storageService;
    }


    public function load($domains_id, $template, $data): string
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname($template));
        $twig = new \Twig\Environment($loader, [
            'cache' => $this->storageService->endpoint($domains_id, 'cache'),
        ]);

        $template = $twig->load($template);

        return $template->render($data);
    }
}
