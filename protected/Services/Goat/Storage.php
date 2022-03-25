<?php
namespace Goat;

class Storage
{
    protected $root, $endpoints;

    use \GoatCore\Traits\Validator;
    use \GoatCore\Traits\Disk;

    public function __construct($root)
    {
        $this->root = $root;
    }


    public function for($domains_id, $endpoint): string
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

        return $this->root . $this->enumeratePath(ark($endpoints, $endpoint, $endpoints['temp']));
    }


    public function dir($domains_id, $relativePath = '', $type = 'public'): array
    {
        $data =  [
            'status' => false,
            'dir'   => [],
            'path'  => '',
        ];

        if ($type === 'content') {

            $dir = $this->for($domains_id, 'public') . DIRECTORY_SEPARATOR . 'content' . $this->enumeratePath($relativePath);

            if ($this->isDir($dir)) {

                $content = $this->listDirInfo($dir);

                $data = [
                    'status' => true,
                    'dir'   => $content,
                    'path'  => $dir,
                ];
            }
        }


        if ($type === 'cache') {

            $dir = $this->for($domains_id, 'public') . DIRECTORY_SEPARATOR . 'cache' . $this->enumeratePath($relativePath);

            if ($this->isDir($dir)) {

                $content = $this->listDirInfo($dir);

                $data = [
                    'status' => true,
                    'dir'   => $content,
                    'path'  => $dir,
                ];
            }
        }


        if ($type === 'public') {

            $dir = $this->for($domains_id, 'public') . $this->enumeratePath($relativePath);

            if ($this->isDir($dir)) {

                $content = $this->listDirInfo($dir);

                $data = [
                    'status' => true,
                    'dir'   => $content,
                    'path'  => $dir,
                ];
            }
        }


        if ($type === 'storage') {

            $dir = $this->for($domains_id, 'storage') . $this->enumeratePath($relativePath);

            if ($this->isDir($dir)) {

                $content = $this->listDirInfo($dir);

                $data = [
                    'status' => true,
                    'dir'   => $content,
                    'path'  => $dir,
                ];
            }
        }

        if ($type === 'temp') {

            $dir = $this->for($domains_id, 'temp') . $this->enumeratePath($relativePath);

            if ($this->isDir($dir)) {

                $content = $this->listDirInfo($dir, false);

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
        $l = mb_strlen($this->root);
        return $this->unc2s(mb_substr($path, $l));
    }


    public function enumeratePath($pathArray): string
    {
        if ($this->string($pathArray) === true) {

            $pathArray = explode('/', $pathArray);
        }

        $pathArray = array_filter($pathArray);

        return count($pathArray)>0 ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pathArray): '';
    }
}
