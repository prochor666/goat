<?php
namespace Goat;

class Thumbnail
{
    protected $config, $imageService, $storageService;

    use \GoatCore\Traits\Disk;

    public function __construct($config, $imageService, $storageService)
    {
        $this->config = $config;
        $this->imageService = $imageService;
        $this->storageService = $storageService;
    }


    public function load($path, $cacheDir): array
    {
        $result = [];

        foreach($this->config['sizes'] as $prefix => $size) {

            $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $prefix . '-' .basename($path);
            $result[$prefix] = $this->isFile($cacheFile) ? $this->storageService->httpPath($cacheFile): false;
        }

        return $result;
    }


    public function create($path, $cacheDir): void
    {
        if ($this->imageService->isImage($path)) {

            $this->imageService->load($path);

            if ($this->imageService->getResource() !== null) {

                foreach(array_reverse($this->config['sizes']) as $prefix => $size) {

                    $this->imageService->compression = $size[1];

                    $this->imageService->imageTarget = $cacheDir . DIRECTORY_SEPARATOR . $prefix . '-' .basename($path);

                    if (!$this->isFile($this->imageService->imageTarget)) {

                        $this->imageService->resizeToWidth($size[0]);
                        $this->imageService->save();
                    }
                }
            }
        }
    }


    public function delete($path, $cacheDir): void
    {
        foreach($this->config['sizes'] as $prefix => $size) {

            $this->deleteFile($cacheDir . DIRECTORY_SEPARATOR . $prefix . '-' .basename($path));
        }
    }
}
