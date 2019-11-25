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
 * Class ToBoolTest
 * @package Syrnik\WaShippingUtils\Tests
 */
class ToBoolTest extends TestCase
{
    /**
     * @param $value
     * @param $expected
     * @dataProvider booleanValues
     */
    public function testToBoolDefaultSet($value, $expected, $message)
    {
        $this->assertEquals($expected, WaShippingUtils::toBool($value), $message);
    }

    public function booleanValues()
    {
        return [
            ['Да', true, 'Да <> true'],
            ['дА', true, 'дА <> true'],
            ['нЕт', false, 'нЕт <> false'],
            ['on', true, 'on <> true'],
            [true, true, 'true <> true'],
            [0, false, '0 <> false'],
            [1, true, '1 <> true']
        ];
    }
}