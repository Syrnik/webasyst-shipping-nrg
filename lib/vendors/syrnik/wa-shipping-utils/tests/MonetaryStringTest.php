<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace Syrnik\WaShippingUtils\Tests;

use PHPUnit\Framework\TestCase;
use Syrnik\WaShippingUtils;

class MonetaryStringTest extends TestCase
{
    /**
     * @dataProvider floatsData
     * @param $value
     * @param $expected
     */
    public function testMonetaryString($value, $expected)
    {
        $this->assertEquals($expected, WaShippingUtils::monetaryString($value));
    }

    public function floatsData()
    {
        return array(
            [1.23, '1.23'],
            [1.2345, '1.23'],
            [1.2, '1.20'],
            [1, '1.00'],
            [1.0, '1.00'],
            [0.5, '0.50']
        );
    }
}