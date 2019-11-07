<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace Syrnik\WaShippingUtils\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Syrnik\WaShippingUtils;

/**
 * Class CalcDaysToShipTest
 * @package Syrnik\WaShippingUtils\Tests
 */
class CalcDaysToShipTest extends TestCase
{
    /**
     * @dataProvider calcData
     *
     * @param array $data
     * @param $expected
     * @throws Exception
     */
    public function testCalcDaysToShip(array $data, $expected)
    {
        /**
         * @var int $limit_hour
         * @var int $add_days
         * @var array $weekdays
         * @var array $params
         */
        extract($data);
        $this->assertEquals($expected, WaShippingUtils::calcDaysToShip($limit_hour, $add_days, $weekdays, $params));
    }

    public function calcData()
    {
        return [
            [['limit_hour'=>0, 'add_days'=>0, 'weekdays'=>['1', '2', '3', '4', '5', '6', '7'], 'params'=>[]], 0],
            [['limit_hour'=>0, 'add_days'=>1, 'weekdays'=>['1', '2', '3', '4', '5', '6', '7'], 'params'=>[]], 1]
        ];
    }
}