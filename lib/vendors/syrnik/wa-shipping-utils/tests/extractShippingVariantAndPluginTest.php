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
 * Class extractShippingVariantAndPluginTest
 * @package Syrnik\WaShippingUtils\Tests
 */
class extractShippingVariantAndPluginTest extends TestCase
{
    /**
     * @dataProvider VariantSets
     * @param $first_param
     * @param $second_param
     * @param $expected
     */
    public function testExtractShippingVariantAndPlugin($first_param, $second_param, $expected, $message)
    {
        $result = WaShippingUtils::extractShippingVariantAndPlugin($first_param, $second_param);
        $this->assertEquals('object', gettype($result), $message);
        $this->assertObjectHasAttribute('plugin', $result, $message);
        $this->assertObjectHasAttribute('variant', $result, $message);
        $this->assertObjectHasAttribute('error', $result, $message);
        $this->assertEquals($expected->plugin, $result->plugin, $message);
        $this->assertEquals($expected->variant, $result->variant, $message);
        $this->assertEquals($expected->error, $result->error, $message);
    }

    /**
     * @return array
     */
    public function VariantSets()
    {
        return [
            ['point', null, (object)['plugin' => '', 'variant' => 'point', 'error' => false], 'строка'],
            [['shipping_plugin' => 'plugin', 'shipping_rate_id' => 'point'], null, (object)['plugin' => 'plugin', 'variant' => 'point', 'error' => false], 'массив без ID плагина'],
            [['shipping_plugin' => 'plugin', 'shipping_rate_id' => 'point'], 'plugin', (object)['plugin' => 'plugin', 'variant' => 'point', 'error' => false], 'массив с id плагина'],
            [['shipping_plugin' => 'plugin', 'shipping_rate_id' => 'point'], 'notplugin', (object)['plugin' => 'plugin', 'variant' => 'point', 'error' => true], 'массив с несовпадающим id плагина'],
            [['params' => ['shipping_plugin' => 'plugin', 'shipping_rate_id' => 'point']], 'plugin', (object)['plugin' => 'plugin', 'variant' => 'point', 'error' => false], 'массив с ключом params и id плагина'],
            [['params' => ['shipping_plugin' => 'plugin', 'shipping_rate_id' => 'point']], 'notplugin', (object)['plugin' => 'plugin', 'variant' => 'point', 'error' => true], 'массив с ключом params и несовпадающим id плагина'],
            [[], 'plugin', (object)['plugin' => '', 'variant' => '', 'error' => true], 'пустой массив'],
        ];
    }
}