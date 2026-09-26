<?php

use PHPUnit\Framework\TestCase;

/**
 * Скрытие способа доставки по индексу получателя: код города берётся из ответа
 * search/city вида {"city":{"name":"…","id":383}} (NRG-605).
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */
class nrgShippingAllowedAddressTest extends TestCase
{
    private const KEY = 'allowed-address-test';

    private const SENDER_CITY_CODE = 1;

    private const ADDRESS = ['country' => 'rus', 'city' => '', 'zip' => '630000'];

    /**
     * «Любой недоступный город»: найденный по индексу чужой город не скрывает способ доставки.
     */
    public function testAlwaysAllowsFoundCity()
    {
        $plugin = $this->plugin('always', ['city' => ['name' => 'Новосибирск', 'id' => 383]]);

        self::assertTrue($plugin->isAllowedAddress(self::ADDRESS));
    }

    /**
     * «Любой недоступный город»: город по индексу не найден — способ доставки скрыт.
     */
    public function testAlwaysHidesUnknownCity()
    {
        $plugin = $this->plugin('always', []);

        self::assertFalse($plugin->isAllowedAddress(self::ADDRESS));
    }

    /**
     * «Только город-отправитель»: индекс относится к городу отправителя — способ доставки скрыт,
     * даже если название города в адресе не указано или не совпадает.
     */
    public function testSenderOnlyHidesSenderCityByZip()
    {
        $plugin = $this->plugin('sender_only', ['city' => ['name' => 'Москва', 'id' => self::SENDER_CITY_CODE]]);

        self::assertFalse($plugin->isAllowedAddress(self::ADDRESS));
    }

    public function testSenderOnlyAllowsOtherCity()
    {
        $plugin = $this->plugin('sender_only', ['city' => ['name' => 'Новосибирск', 'id' => 383]]);

        self::assertTrue($plugin->isAllowedAddress(self::ADDRESS));
    }

    private function plugin(string $city_hide, array $search_city_response): nrgShipping
    {
        $adapter = new waAppShippingFixture();
        $adapter->applySettings(self::KEY, [
            'city_hide'        => $city_hide,
            'sender_city_code' => (string)self::SENDER_CITY_CODE,
            'sender_city_name' => 'Москва',
        ]);

        /** @var nrgShipping $plugin */
        $plugin = waShipping::factory('nrg', self::KEY, $adapter);

        return $plugin->setEnergyAPI(new class($search_city_response) extends nrgShippingEnergyAPI {
            private array $response;

            public function __construct(array $response)
            {
                parent::__construct();
                $this->response = $response;
            }

            public function search_city(string $zip): array
            {
                return $this->response;
            }
        });
    }
}
