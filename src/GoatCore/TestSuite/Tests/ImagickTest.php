<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ImagickTest extends TestCase
{
    private $imagick;

    public function testLib(): void
    {
        $this->imagick = new Imagick();
        $this->imagick->newImage(1, 1, new ImagickPixel('#ffffff'));
        $this->imagick->setImageFormat('png');
        $pngData = $this->imagick->getImagesBlob();
        $this->assertSame(0, strpos($pngData, "\x89PNG\r\n\x1a\n"));
    }
}