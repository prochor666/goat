<?php
namespace Goat;

class Storage
{
    protected $root;

    use \GoatCore\Traits\Validator;
    use \GoatCore\Traits\Disk;

    public function __construct($root)
    {
        $this->root = $root;
    }


    public function dir($domains_id, $relativePath = '', $type = 'public'): array
    {
        $data =  [
            'status' => false,
            'dir'   => [],
            'path'  => '',
        ];

        if ($type === 'content') {

            $dir = $this->domainPublicDir($domains_id) . DIRECTORY_SEPARATOR . 'content' . $this->enumeratePath($relativePath);

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

            $dir = $this->domainPublicDir($domains_id) . DIRECTORY_SEPARATOR . 'cache' . $this->enumeratePath($relativePath);

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

            $dir = $this->domainPublicDir($domains_id) . $this->enumeratePath($relativePath);

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

            $dir = $this->domainStorageDir($domains_id) . $this->enumeratePath($relativePath);

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

            $dir = $this->domainTempDir($domains_id) . $this->enumeratePath($relativePath);

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


    public function domainTempDir($domains_id): string
    {
        return $this->root . $this->enumeratePath([
            'temp',
            'sites',
            $domains_id
        ]);
    }


    public function domainPublicDir($domains_id): string
    {
        return $this->root . $this->enumeratePath([
            'public',
            'sites',
            $domains_id
        ]);
    }


    public function domainContentDir($domains_id): string
    {
        return $this->root . $this->enumeratePath([
            'public',
            'sites',
            $domains_id,
            'content'
        ]);
    }


    public function domainCacheDir($domains_id): string
    {
        return $this->root . $this->enumeratePath([
            'public',
            'sites',
            $domains_id,
            'cache'
        ]);
    }


    public function domainStorageDir($domains_id): string
    {
        return $this->root . $this->enumeratePath([
            'storage',
            'sites',
            $domains_id
        ]);
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
