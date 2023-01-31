<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/* *******************
* Embed app core     *
* ****************** */
require_once('./src/GoatCore/TestSuite/_init.php');

final class NumericTest extends TestCase
{
    use \GoatCore\Traits\Validator;

    public function getDataProvider(): array
    {
        $data = [
            [1, true],
            [0, true],
            [1.3, true],
            ['1,3', false],
            ['1.3', true],
            ['2', true],
            [0.00000000034274, true],
            [PHP_FLOAT_MIN, true],
            [PHP_FLOAT_MAX, true],
            [PHP_INT_MIN, true],
            [PHP_INT_MAX, true],
            ['john', false],
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

        $this->assertSame($expect, $this->numeric($data), "assertSame: $dataMem " . ($expect ? "true": "false" ) ."");
    }
}