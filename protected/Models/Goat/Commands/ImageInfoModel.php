<?php
namespace Goat\Commands;

use GoatCore\GoatCore;

/**
* ImageInfoModel - imageinfo command model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class ImageInfoModel
{
    protected $app;

    protected $input;

    protected $data;

    protected $imageService;

    public function __construct(GoatCore $app, $input = [])
    {
        $this->app = $app;
        $this->input = $input;
        $this->imageService = $this->app->store->entry('Goat\Image');
    }


    public function release(): array
    {
        $image = !ark($this->input, 'image', false) ? ark($this->input, 'i', false): ark($this->input, 'image', false);
        $isImage = $this->imageService->isImage($image);

        if ($image !== false && $isImage === true) {

            $this->imageService->fixExifRotation = false;
            $this->imageService->clearMeta = false;
            $this->imageService->load($image);
            $this->imageService->imageTarget = $this->app->config('fsRoot') . '/temp/conv.' . $this->imageService->type();

            $this->imageService->resizeToHeight(200);
            $this->imageService->save();

            $this->data = [
                'type' => $this->imageService->type(),
                'geometry' => [$this->imageService->width(), $this->imageService->height()],
                'exif' => $this->imageService->getExif(),
                'target' => $this->imageService->imageTarget,
                'input' => $this->input,
            ];

            $this->imageService->close();
        } else {

            $this->data = [
                'error' => $isImage === true ? 'Param --image [-i] is required': 'This file is not an image',
                'input' => $this->input,
            ];
        }

        return $this->data;
    }
}
