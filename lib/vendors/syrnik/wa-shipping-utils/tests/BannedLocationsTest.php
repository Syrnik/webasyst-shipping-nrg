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
 * Class BannedLocationsTest
 */
class BannedLocationsTest extends TestCase
{
    /**
     * @param $data
     * @param $expected
     * @dataProvider data
     */
    public function testBannedLocations($data, $expected)
    {
        /**
         * @var string $city
         * @var string $region_code
         * @var string $template
         */
        extract($data);

        $this->assertEquals($expected, WaShippingUtils::isBannedLocation($city, $region_code, $template));
    }

    public function data()
    {
        return [
            [['city'=>'Москва', 'region_code'=>'77', 'template'=>'москва'], true],
            [['city'=>'Москва', 'region_code'=>'77', 'template'=>'москва:'], true],
            [['city'=>'москва', 'region_code'=>'77', 'template'=>'москва'], true],
            [['city'=>'москва', 'region_code'=>'77', 'template'=>'москва:'], true],
            [['city'=>'москва', 'region_code'=>'77', 'template'=>'москва:77'], true],
            [['city'=>'москва', 'region_code'=>'77', 'template'=>'москва:78'], false],
            [['city'=>'самара', 'region_code'=>'77', 'template'=>'москва:77'], false],
            [['city'=>'самара', 'region_code'=>'77', 'template'=>':77'], true],
            [['city'=>'самара', 'region_code'=>'77', 'template'=>' '], false],
            [['city'=>'самара', 'region_code'=>'77', 'template'=>' ; ;'], false],
            [['city'=>'самара', 'region_code'=>'77', 'template'=>':'], false],
            [['city'=>'москва', 'region_code'=>'77', 'template'=>'самара;:77'], true],
            [['city'=>'москва', 'region_code'=>'77', 'template'=>'самара;:78'], false],
        ];
    }
}