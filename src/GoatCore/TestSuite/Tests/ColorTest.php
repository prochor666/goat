<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/* *******************
* Embed app core     *
* ****************** */
require_once('./src/GoatCore/TestSuite/_init.php');

final class ColorTest extends TestCase
{
    use \GoatCore\Traits\Validator;

    public function getDataProvider(): array
    {
        $data = [
            ['#A12C', true],
            ['#FF00AA22', true],
            ['#FF00AA', true],
            ['#DF0', true],
            ['#aaa000', true],
            ['#fh0' ,false],
            ['#123', true],
            ['rgb(11, 20, 10)', true],
            ['RGB(0,0,255)', true],
            ['rgb(0,0,256)', false],
            ['rgb(a,d,c)', false],
            ['RGBA(101, 20, 150, 0.5)', true],
            ['rgba(1,1,1, 2)', false],
            ['RGBA(99, 189, 12, 0.67)', true],
            ['RGBA(10%,20%,150%, 2.5)', false],
            ['hsl(235, 100%, 20%)', true],
            ['HSL(235, 100%, .5)', false],
            ['hsl(220, 500, 20%)', false],
            ['hsl(13%, 100%, 20%)', false],
            ['hsla(235, 100%, 13, .05)', false],
            ['hsla(235, 100%, 20%, .15)', true],
            ['hsla(112, 100%, 90%, 2)', false],
            ['hsla(235, 100%, 20%, 100%)', false],
            ['adsasd', false],
        ];

        return $data;
    }

    /**
    * @dataProvider getDataProvider
    */
    public function testRun($data, $expect): void
    {
        $dataMem = !is_array($data) ? (string)$data: gettype($data);
        
        $this->assertSame($expect, $this->color($data), "assertSame: $dataMem " . ($expect ? "true": "false" ) ."");
    }
}