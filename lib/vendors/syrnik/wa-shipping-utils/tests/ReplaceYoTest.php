<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace Syrnik\WaShippingUtils\Tests;

use PHPUnit\Framework\TestCase;
use Syrnik\WaShippingUtils;

class ReplaceYoTest extends TestCase
{
    /**
     * @param $value
     * @param $expected
     * @dataProvider stringData
     */
    public function testReplaceYo($value, $expected)
    {
        $this->assertEquals($expected, WaShippingUtils::replaceYo($value));
    }

    public function stringData()
    {
        return [
            ['прёт', 'прет'],
            ['ПРЁТ', 'ПРЕТ'],
            ['переёлка', 'переелка'],
            ['ПЕРЕЁЛКА', 'ПЕРЕЕЛКА']
        ];
    }
}