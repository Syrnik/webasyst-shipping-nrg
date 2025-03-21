<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

declare(strict_types=1);

namespace Syrnik\WaShippingUtils\Tests;

use PHPUnit\Framework\TestCase;
use Syrnik\WaShippingUtils;
use Syrnik\WaShippingUtils\CalcTotalCostException;

class CalcTotalCostTest extends TestCase
{
    /**
     * @dataProvider validFormulasData
     * @param array $data
     * @param $expected
     */
    public function testCalcTotalCost(array $data, $expected)
    {
        /**
         * @var int|float $carrier_cost
         * @var int|float $total_price
         * @var int|float $total_raw_price
         * @var string $handling_cost
         * @var string $handling_base
         * @var string $free
         */
        extract($data);

        $this->assertEquals($expected, WaShippingUtils::calcTotalCost($carrier_cost, $total_price, $total_raw_price, $handling_cost, $handling_base, $free));
    }

    public function validFormulasData()
    {
        return [
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'0', 'handling_base'=>'shipping', 'free'=>''], 100],
            // free delivery
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'0', 'handling_base'=>'shipping', 'free'=>'500'], 0],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'0', 'handling_base'=>'shipping', 'free'=>'1500'], 100],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'10', 'handling_base'=>'shipping', 'free'=>''], 110],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'20%', 'handling_base'=>'shipping', 'free'=>''], 120],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'20%', 'handling_base'=>'order', 'free'=>''], 300],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'20%', 'handling_base'=>'order_shipping', 'free'=>''], 320],
            // doubled to test internal cache
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'20%', 'handling_base'=>'order_shipping', 'free'=>''], 320],
            // single percent sign
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'%', 'handling_base'=>'order_shipping', 'free'=>''], 100],
            // formulas
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'s+s*0.1', 'handling_base'=>'formula', 'free'=>''], 110],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'s+(s+z)*0.05', 'handling_base'=>'formula', 'free'=>''], 155],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'s+(s+y)*0.05', 'handling_base'=>'formula', 'free'=>''], 205],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'S+(s+Y)*0.05', 'handling_base'=>'formula', 'free'=>''], 205],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'s-300', 'handling_base'=>'formula', 'free'=>''], 0],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'-500', 'handling_base'=>'formula', 'free'=>''], 0],
            [['carrier_cost'=>100, 'total_price'=>1000, 'total_raw_price'=>2000, 'handling_cost'=>'100', 'handling_base'=>'formula', 'free'=>''], 100],
            [['carrier_cost'=>322.29, 'total_price'=>57, 'total_raw_price'=>57, 'handling_cost'=>'20+0.1*S+0.005*Y', 'handling_base'=>'formula', 'free'=>''], 52.51],
        ];
    }

    public function testEmptyFormula()
    {
        $result = WaShippingUtils::calcTotalCost(100, 1000, 2000, '0', 'formula', '');
        $this->assertEquals(0, $result);

        $result = WaShippingUtils::calcTotalCost(100, 1000, 2000, '', 'formula', '');
        $this->assertEquals(100, $result);

    }

    public function testFormulaExceptions()
    {
        $this->expectException(CalcTotalCostException::class);
        $res = WaShippingUtils::calcTotalCost(100, 1000, 2000, 's+s0.1', 'formula');
    }
}
