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
 * Class RoundPriceTest
 */
class RoundPriceTest extends TestCase
{
    /**
     * @dataProvider roundingData
     */
    public function testRounding($value, $expected)
    {
        $this->assertEquals($expected, WaShippingUtils::roundPrice($value['price'], $value['round_setting'], $value['round_strategy']));
    }

    public function roundingData()
    {
        return [
            [['price' => 1.23, 'round_setting' => '0.01', 'round_strategy' => 'std'], 1.23],
            [['price' => 1.235, 'round_setting' => '0.01', 'round_strategy' => 'std'], 1.24],
            [['price' => 1.2345, 'round_setting' => '0.01', 'round_strategy' => 'std'], 1.23],
            [['price' => 1.2345, 'round_setting' => '0.01', 'round_strategy' => 'up'], 1.24],
            [['price' => 1.231, 'round_setting' => '0.01', 'round_strategy' => 'up'], 1.24],
            [['price' => 1.231, 'round_setting' => '0.01', 'round_strategy' => 'down'], 1.23],
            [['price' => 1.239, 'round_setting' => '0.01', 'round_strategy' => 'down'], 1.23],

            [['price' => 1.23, 'round_setting' => '0.1', 'round_strategy' => 'std'], 1.2],
            [['price' => 1.235, 'round_setting' => '0.1', 'round_strategy' => 'std'], 1.2],
            [['price' => 1.2345, 'round_setting' => '0.1', 'round_strategy' => 'std'], 1.2],
            [['price' => 1.2345, 'round_setting' => '0.1', 'round_strategy' => 'up'], 1.3],
            [['price' => 1.231, 'round_setting' => '0.1', 'round_strategy' => 'up'], 1.3],
            [['price' => 1.231, 'round_setting' => '0.1', 'round_strategy' => 'down'], 1.2],
            [['price' => 1.239, 'round_setting' => '0.1', 'round_strategy' => 'down'], 1.2],

            [['price' => 1.23, 'round_setting' => '1', 'round_strategy' => 'std'], 1],
            [['price' => 1.5, 'round_setting' => '1', 'round_strategy' => 'std'], 2],
            [['price' => 1.4, 'round_setting' => '1', 'round_strategy' => 'std'], 1],
            [['price' => 1.2345, 'round_setting' => '1', 'round_strategy' => 'up'], 2],
            [['price' => 1.231, 'round_setting' => '1', 'round_strategy' => 'up'], 2],
            [['price' => 1.9, 'round_setting' => '1', 'round_strategy' => 'down'], 1],
            [['price' => 1.239, 'round_setting' => '1', 'round_strategy' => 'down'], 1],
        ];
    }
}