<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/* *******************
* Embed app core     *
* ****************** */
require_once('./src/GoatCore/TestSuite/_init.php');

final class UrlTest extends TestCase
{
    use \GoatCore\Traits\Validator;

    public function getDataProvider(): array
    {
        $data = [
            ['https://google.com', true],
            [0, false],
            ['google.com', false],
            ['_2', false],
            ['localhost', false],
            [PHP_FLOAT_MIN, false],
            ['sftp://john', true],
            ['http://localhost', true],
            [[], false],
        ];

        return $data;
    }

    /**
    * @dataProvider getDataProvider
    */
    public function testRun($data, $expect): void
    {
        $dataMem = !is_array($data) ? (string)$data: gettype($data);
        /* 
        echo "Running: " . $dataMem . " > " . ($expect ? "true": "false" ) ."\n";
        var_dump($this->url($data));
        echo "-------------------------------------------------------------\n\n"; 
        */
        $this->assertSame($expect, $this->url($data), "assertSame: $dataMem " . ($expect ? "true": "false" ) ."");
    }
}