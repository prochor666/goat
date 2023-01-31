<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/* *******************
* Embed app core     *
* ****************** */
require_once('./src/GoatCore/TestSuite/_init.php');

final class IntTest extends TestCase
{
    use \GoatCore\Traits\Validator;

    public function getDataProvider(): array
    {
        $data = [
            [1, true],
            [0, true],
            [1.3, false],
            ['2', false],
            [0.00000000034274, false],
            [PHP_FLOAT_MIN, false],
            [PHP_FLOAT_MAX, false],
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

        $this->assertSame($expect, $this->int($data), "assertSame: $dataMem " . ($expect ? "true": "false" ) ."");
    }
}