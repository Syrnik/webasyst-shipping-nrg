<?php

use PHPUnit\Framework\TestCase;
use SergeR\Util\EvalMath\EvalMath;
use Syrnik\WaShippingUtils;

/**
 * Smoke-тест окружения: фреймворк поднят, плагин создаётся через фабрику,
 * автозагрузка классов плагина и vendor-библиотек работает.
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */
class nrgShippingEnvironmentTest extends TestCase
{
    public function testPluginInstanceIsCreatedByFactory()
    {
        $plugin = waShipping::factory('nrg', 1, new waAppShippingFixture());

        self::assertInstanceOf(nrgShipping::class, $plugin);
        self::assertSame('RUB', $plugin->allowedCurrency());
        self::assertSame('kg', $plugin->allowedWeightUnit());
    }

    public function testPluginClassesAreAutoloaded()
    {
        self::assertTrue(class_exists(nrgShippingEstimatedDelivery::class));
        self::assertTrue(class_exists(nrgShippingEnergyAPI::class));
    }

    public function testVendorClassesAreAutoloaded()
    {
        self::assertTrue(class_exists(WaShippingUtils::class));
        self::assertTrue(class_exists(EvalMath::class));
    }

    public function testEstimatedDeliveryParsesRange()
    {
        $delivery = (new nrgShippingEstimatedDelivery())->parseRegexRange('3-5 дней');

        self::assertSame(3, $delivery->getMinDays());
        self::assertSame(5, $delivery->getMaxDays());
    }
}
