<?php
namespace Goat;

class Storage
{
    protected $root, $endpoints;

    use \GoatCore\Traits\Disk;

    public function __construct($root)
    {
        $this->root = $root;
    }


    public function endpoint($domains_id, $endpoint): string
    {
        // Domain storage endpoints
        $endpoints = [
            'temp' => ['temp', 'sites', $domains_id],
            'public' => ['public', 'sites', $domains_id],
            'content' => ['public', 'sites', $domains_id, 'content'],
            'cache' => ['public', 'sites', $domains_id, 'cache'],
            'storage' => ['storage', 'sites', $domains_id],
            'templates' => ['storage', 'sites', $domains_id, 'templates'],
        ];

        return $this->enumeratePath(ark($endpoints, $endpoint, $endpoints['temp']));
    }


    public function dir($domains_id, $relativePath = '', $type = 'public'): array
    {
        $data =  [
            'status' => false,
            'dir'   => [],
            'path'  => '',
        ];

        $dir = false;

        if ($type === 'content') {

            $dir = $this->endpoint($domains_id, 'public').'/content/'.$this->enumeratePath($relativePath);
        }


        if ($type === 'cache') {

            $dir = $this->endpoint($domains_id, 'public').'/cache/'.$this->enumeratePath($relativePath);
        }


        if ($type === 'public') {

            $dir = $this->endpoint($domains_id, 'public').'/'.$this->enumeratePath($relativePath);
        }


        if ($type === 'storage') {

            $dir = $this->endpoint($domains_id, 'storage').'/'.$this->enumeratePath($relativePath);
        }

        if ($type === 'temp') {

            $dir = $this->endpoint($domains_id, 'temp').'/'.$this->enumeratePath($relativePath);
        }

        if ($dir !== false) {

            //$dir = trim($dir, '\\');

            if ($this->isDir($dir)) {

                $content = $this->listDirInfo($dir);

                $data = [
                    'status' => true,
                    'dir'   => $content,
                    'path'  => $dir,
                ];
            }
        }

        return $data;
    }


    public function httpPath($path): string
    {
        return $path;
        /* $l = mb_strlen($this->root);
        return mb_substr($path, $l); */
    }


    public function enumeratePath($path): string
    {
        if (is_array($path)) {

            return implode('/', array_filter($path));
        }

        return $path;
    }
}
