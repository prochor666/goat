<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/* *******************
* Embed app core     *
* ****************** */
require_once('./src/GoatCore/TestSuite/_init.php');

final class DomainTest extends TestCase
{
    use \GoatCore\Traits\Validator;

    public function getDataProvider(): array
    {
        $data = [
            ['https://google.com', false],
            [0, false],
            ['google.com', true],
            ['_2', false],
            ['google.co.uk', true],
            [PHP_FLOAT_MIN, false],
            ['john', true],
            ['localhost', true],
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
        var_dump($this->domain($data));
        echo "-------------------------------------------------------------\n\n"; 
        */
        $this->assertSame($expect, $this->domain($data), "assertSame: $dataMem " . ($expect ? "true": "false" ) ."");
    }
}