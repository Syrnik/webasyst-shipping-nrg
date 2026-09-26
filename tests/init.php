<?php
/**
 * Bootstrap для PHPUnit.
 *
 * Самодостаточный: composer install и dev-зависимости не нужны.
 * Запуск из корня плагина глобально установленным PHPUnit: phpunit -c phpunit.xml
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */

error_reporting(E_ALL | E_NOTICE);

require_once(dirname(__FILE__, 5) . '/wa-config/SystemConfig.class.php');
waSystem::getInstance(null, new SystemConfig());

// Адаптер хостового приложения. nrg — системный плагин, а не плагин приложения,
// поэтому нет хостового wa('appid'), который подтянул бы классы плагина сам:
// адаптер приходится подставлять руками.
require_once(__DIR__ . '/waAppShippingFixture.class.php');

// Прогрев автозагрузчика плагина — один раз для всех тестов.
// Конструктор waSystemPlugin регистрирует lib/classes/** в waAutoload,
// а nrgShipping::init() подключает lib/vendors/autoload.php (Syrnik\*, SergeR\*).
// Без этого классы плагина в тестах не находятся.
waShipping::factory('nrg', 1, new waAppShippingFixture());
