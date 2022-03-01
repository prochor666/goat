<?php
namespace GoatCore\Images;

/**
* GoatCore\ImageMagick - Imagemagick version of image manipulation class, resize/convert/save images
* @author Jan Prochazka, prochor666 <prochor666@gmail.com>
*/
class ImageMagick
{
    public $imageSource, $imageTarget, $compression, $permissions, $fixExifRotation;
    protected $image, $imageInfo, $imageType, $exif;

    /**
    * Image class contructor, default value set
    * @param void
    * @return void
    */
    public function __construct()
    {
        $this->image = null;
        $this->exif = false;
        $this->imageType = null;
        $this->imageInfo = [];
        $this->imageSource = null;
        $this->imageTarget = null;
        $this->compression = 100;
        $this->permissions = 0777;
        $this->fixExifRotation = true;
        $this->clearMeta = true;
    }


    /**
    * Image load and parse type
    * @param void
    * @return void
    */
    public function load($path)
    {
        $this->imageSource = $path;
        $this->imageTarget = $path;

        if (file_exists($this->imageSource)) {

            $this->image = new \Imagick($this->imageSource);
            $this->imageType = strtolower($this->image->getImageFormat());

            if ($this->imageType !== false) {

                switch ($this->imageType) {

                    case 'jpeg': case 'jpe': case 'jpg':

                        $this->exif = $this->image->getImageProperties("exif:*");
                        $this->fix();
                        $this->metaClear();

                        break;

                    case 'png':

                        break;

                    case 'gif':


                        break;

                    default:

                        $this->image = null;
                }
            }
        }
    }


    /**
    * EXIF rotation fix
    * @param void
    * @return void
    */
    protected function fix() {

        if ($this->fixExifRotation === true) {

            $orientation = $this->image->getImageOrientation();

            switch ($orientation) {
                case \Imagick::ORIENTATION_BOTTOMRIGHT:
                    $this->image->rotateimage("#000", 180); // rotate 180 degrees
                break;

                case \Imagick::ORIENTATION_RIGHTTOP:
                    $this->image->rotateimage("#000", 90); // rotate 90 degrees CW
                break;

                case \Imagick::ORIENTATION_LEFTBOTTOM:
                    $this->image->rotateimage("#000", -90); // rotate 90 degrees CCW
                break;
            }
        }
    }


    /**
    * Clear jpeg metadata
    * @param void
    * @return void
    */
    protected function metaClear() {

        if ($this->clearMeta === true) {

            // This will reset all metadata
            $this->image->stripImage();
            $this->image->writeImage();
        }
    }


    /**
    * Image resource getter
    * @param void
    * @return resource|bool
    */
    public function getResource()
    {
        return $this->image;
    }


    /**
    * Image resource getter
    * @param void
    * @return array|bool
    */
    public function getExif()
    {
        return $this->exif;
    }


    /**
    * Image processing, save buffer or output image result
    * @param bool $save
    * @return bool
    */
    protected function process($save = true)
    {
        $result = false;

        if ($this->image !== false) {

            if ($save === true) {

                //$result = (bool)file_put_contents($this->imageTarget, $this->show());
                $this->image->writeImage($this->imageTarget);

                if ( $this->permissions != null ) {

                    umask(0000);
                    chmod($this->imageTarget, $this->permissions);
                }

            }else{

                $result = $this->show();
            }
        }

        return $result;
    }


    /**
    * Output image reource
    * @param void
    * @return void
    */
    protected function show()
    {
        // Make buffer
        ob_start();
        switch ($this->imageType) {

            case 'jpeg': case 'jpe': case 'jpg':

                $this->image->getImageBlob();
                break;

            case 'png':

                $this->image->getImageBlob();
                break;

            case 'gif':

                $this->image->getImageBlob();
                break;

            default:

                echo 'IMAGE TYPE ERROR';
        }

        return ob_get_clean();
    }


    /**
    * File conversion, set image type to force format change
    * @param void
    * @return bool
    */
    public function convert($format = 'jpg')
    {
        if ($this->imageType === 'png') {

            // set background to white (Imagick doesn't know how to deal with transparent background if you don't instruct it)
            $this->image->setImageBackgroundColor(new \ImagickPixel('white'));

            // flattens multiple layers
            $this->image = $this->image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }

        $this->imageType = $format;

        $this->image->setImageFormat($format);
        if ($format === 'jpg' || $format === 'jpeg' || $format === 'jpe') {

            $this->image->setImageCompression(\imagick::COMPRESSION_JPEG);
        }
        $this->image->setCompressionQuality($this->compression);

        return $this->process(true);
    }


    /**
    * Save image resource to a file
    * @param void
    * @return bool
    */
    public function save()
    {
        return $this->process(true);
    }


    /**
    * Outuput raw image resource with proper header
    * @param void
    * @return bool
    */
    public function output()
    {
        switch ($this->imageType) {

            case 'jpeg': case 'jpe': case 'jpg':

                header('Content-Type: image/jpeg');
                break;

            case 'png':

                header('Content-Type: image/png');
                break;

            case 'gif':

                header('Content-Type: image/gif');
        }

        return $this->process(false);
    }


    /**
    * Get image width
    * @param void
    * @return int
    */
    public function width()
    {
        $geometry = $this->image->getImageGeometry();
        return (int)ark($geometry, 'width', 0);
    }


    /**
    * Get image height
    * @param void
    * @return int
    */
    public function height()
    {
        $geometry = $this->image->getImageGeometry();
        return (int)ark($geometry, 'height', 0);
    }


    /**
    * Get image type
    * @param void
    * @return string
    */
    public function type()
    {
        return $this->imageType;
    }


    /**
    * Resize image, respect input height, set proper aspect ratio and calculate width
    * @param int $height
    * @return int
    */
    public function resizeToHeight($height)
    {
        $ratio = $height / $this->height();
        $width = $this->width() * $ratio;
        $this->resize($width, $height);
    }


    /**
    * Resize image, respect input width, set proper aspect ratio and calculate height
    * @param int $height
    * @return int
    */
    public function resizeToWidth($width)
    {
        $ratio = $width / $this->width();
        $height = $this->height() * $ratio;
        $this->resize($width,$height);
    }


    /**
    * Flip image by mode 1,2,3
    * @param int $mode
    * @return void
    */
    public function flip($mode = -1)
    {
        switch ($mode) {

            case IMG_FLIP_HORIZONTAL:

                $this->image->flopImage();
                break;

            case IMG_FLIP_VERTICAL:

                $this->image->flipImage();
                break;

            case IMG_FLIP_BOTH:

                $this->image->flipImage();
                $this->image->flopImage();
                break;

            default:
                // unknown mode, do nothing
        }
    }


    /**
    * Rotate image by angle
    * @param float $angle
    * @return void
    */
    public function rotate($angle = 0, $background = '#000000')
    {
        if ((int)$angle !== 0) {

            $this->image->rotateImage($background, $angle);
        }
    }


    /**
    * Resize image to $scale %, based on origin size
    * @param int $scale
    * @return void
    */
    public function scale($scale)
    {
        $width = $this->width() * $scale/100;
        $height = $this->height() * $scale/100;
        $this->resize($width, $height);
    }


    /**
    * Resize image to $width & $height
    * @param int $width
    * @param int $height
    * @return void
    */
    public function resize($width, $height)
    {
        $this->image->resizeImage(
            $width,
            $height,
            \imagick::FILTER_UNDEFINED,
            0,
            false,
            false
        );
    }


    public function isImage($path)
    {
        try {

            $sample = new \Imagick($path);
            $type = strtolower($sample->getImageFormat());
            return in_array($type, ['jpeg', 'jpe', 'jpg', 'png', 'gif']);

        } catch(\Throwable $e) {

            return false;
        }
    }


    /**
    * Close (destroy) image resource
    * @param void
    * @return void
    */
    public function close()
    {
        $this->image->clear();
        $this->image = null;
    }
}
