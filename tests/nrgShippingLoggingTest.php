<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use SergeR\ProcessLogger;

/**
 * Логирование расчёта включается из настроек на время и не зависит от режима отладки (NRG-600).
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */
class nrgShippingLoggingTest extends TestCase
{
    private const KEY = 'logging-test';

    public function testProcessLoggerIsAutoloaded()
    {
        self::assertTrue(class_exists(ProcessLogger::class));
    }

    public function testLoggingIsDisabledByDefault()
    {
        $plugin = $this->plugin();

        self::assertFalse($plugin->isLoggingEnabled());
        self::assertSame(LogLevel::CRITICAL, $this->logLevel($plugin));
    }

    public function testLoggingIsEnabledWithinWindow()
    {
        $plugin = $this->plugin(['logging_duration' => 600, 'logging_until' => time() + 600]);

        self::assertTrue($plugin->isLoggingEnabled());
        self::assertSame(LogLevel::INFO, $this->logLevel($plugin));
    }

    public function testLoggingIsDisabledAfterWindowExpired()
    {
        $plugin = $this->plugin(['logging_duration' => 600, 'logging_until' => time() - 1]);

        self::assertFalse($plugin->isLoggingEnabled());
        self::assertSame(LogLevel::CRITICAL, $this->logLevel($plugin));
    }

    public function testTouchedSelectOpensWindow()
    {
        $adapter = new waAppShippingFixture();
        $plugin = waShipping::factory('nrg', self::KEY, $adapter);

        $before = time();
        $plugin->saveSettings(['logging' => ['duration' => '1800', 'touched' => '1']]);

        $saved = $adapter->getSettings('nrg', self::KEY);
        // Значение самого контрола не сохраняется (waSystemPlugin::saveSettings() лишь дописывает null-дефолт)
        self::assertNull($saved['logging'] ?? null);
        self::assertSame(1800, $saved['logging_duration']);
        self::assertGreaterThanOrEqual($before + 1800, $saved['logging_until']);
        self::assertLessThanOrEqual(time() + 1800, $saved['logging_until']);
    }

    public function testTouchedOffClosesWindow()
    {
        $adapter = new waAppShippingFixture();
        $adapter->applySettings(self::KEY, ['logging_duration' => 600, 'logging_until' => time() + 600]);
        $plugin = waShipping::factory('nrg', self::KEY, $adapter);

        $plugin->saveSettings(['logging' => ['duration' => '0', 'touched' => '1']]);

        $saved = $adapter->getSettings('nrg', self::KEY);
        self::assertSame(0, $saved['logging_duration']);
        self::assertSame(0, $saved['logging_until']);
    }

    public function testUntouchedSelectKeepsWindow()
    {
        $until = time() + 600;
        $adapter = new waAppShippingFixture();
        $adapter->applySettings(self::KEY, ['logging_duration' => 600, 'logging_until' => $until]);
        $plugin = waShipping::factory('nrg', self::KEY, $adapter);

        // Сохранение несвязанных настроек: селект не трогали, выбрано «Выключено» по умолчанию формы
        $plugin->saveSettings(['optimize' => 'cheapest', 'logging' => ['duration' => '0', 'touched' => '0']]);

        $saved = $adapter->getSettings('nrg', self::KEY);
        self::assertSame(600, $saved['logging_duration']);
        self::assertSame($until, $saved['logging_until']);
    }

    private function plugin(array $settings = []): nrgShipping
    {
        $adapter = new waAppShippingFixture();
        $adapter->applySettings(self::KEY, $settings);

        return waShipping::factory('nrg', self::KEY, $adapter);
    }

    private function logLevel(nrgShipping $plugin): ?string
    {
        $logger = $plugin->getLogger();
        self::assertInstanceOf(ProcessLogger::class, $logger);

        return $logger->getLogLevel();
    }
}
