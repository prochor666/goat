<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/* *******************
* Embed app core     *
* ****************** */
require_once('./src/GoatCore/TestSuite/_init.php');

final class EmailTest extends TestCase
{
    use \GoatCore\Traits\Validator;

    public function getDataProvider(): array
    {
        $data = [
            ['prochor666@gmail.com', true],
            ['t#@d.co.uk', true],
            ['df@123', false],
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
        
        $this->assertSame($expect, $this->email($data), "assertSame: $dataMem " . ($expect ? "true": "false" ) ."");
    }
}