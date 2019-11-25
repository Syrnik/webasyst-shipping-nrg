<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace Syrnik\WaShippingUtils\Tests;

use PHPUnit\Framework\TestCase;
use Syrnik\WaShippingUtils;

/**
 * Class StringToFloatTest
 */
class StringToFloatTest extends TestCase
{
    /**
     * @dataProvider stringsToTest
     * @param $string
     * @param $expected
     */
    public function testStrToFloat($string, $expected)
    {
        $this->assertEquals($expected, WaShippingUtils::strToFloat($string));
    }

    public function stringsToTest()
    {
        return [
            ['1,2', 1.2],
            ['=1,2', 0],
            ['1.2=', 1.2],
            ['1.23,4', 1.23],
            ['1,23.40', 1.23]
        ];
    }
}