<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

namespace Syrnik\WaShippingUtils\Tests;

use PHPUnit\Framework\TestCase;
use Syrnik\WaShippingUtils;

class ToFloatTest extends TestCase
{
    /**
     * @param array $value
     * @param float|null $expected
     * @param string $message
     * @return void
     * @dataProvider values
     */
    public function testToFloat(array $value, ?float $expected, string $message)
    {
        $this->assertEquals($expected, WaShippingUtils::toFloat($value[0], $value[1]), $message);
    }

    public function values(): array
    {
        return [
            [['1', false], 1, '\'1\' <> 1'],
            [['1.25', false], 1.25, '\'1.25\' <> 1.25'],
            [['', false], 0, 'not nullable \'\' <> 0'],
            [['', true], null, 'nullable \'\' <> null'],
            [[1.44, false], 1.44, '1.44 <> 1.44'],
            [[1.44, true], 1.44, 'nullable 1.44 <> 1.44'],
            [[null, false], null, 'not nullable NULL <> 0'],
            [[null, true], null, 'nullable NULL <> NULL'],
        ];
    }
}
