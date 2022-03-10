<?php
namespace Goat;

use GoatCore\Images\ImageMagick;
use GoatCore\Images\ImageGD;

if (extension_loaded('imagick') && class_exists('Imagick')) {

    class Image extends ImageMagick
    {
        public function __construct()
        {
            parent::__construct();
        }
    }

} else {

    class Image extends ImageGD
    {
        public function __construct()
        {
            parent::__construct();
        }
    }
}


