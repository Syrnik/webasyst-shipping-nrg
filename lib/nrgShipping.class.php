<?php

/**
 * @property string $delivery_type
 * @property string $optimize
 * @property string $pickup_price
 * @property string $show_first
 * @property string $sender_city_code
 */
class nrgShipping extends waShipping
{

    /**
     *
     * @return string ISO3 currency code or array of ISO3 codes
     */
    public function allowedCurrency()
    {
        return 'RUB';
    }

    /**
     *
     * @return string Weight units or array of weight units
     */
    public function allowedWeightUnit()
    {
        return 'kg';
    }

    public function requestedAddressFields()
    {
        return array('zip' => array('cost' => true, 'required' => true));
    }

    public function saveSettings($settings = array())
    {
        if (array_key_exists('sender_zip', $settings)) {
            $settings['sender_city_code'] = '';
            if (!empty($settings['sender_zip'])) {
                $net = new waNet(array('format' => waNet::FORMAT_JSON, 'verify' => false));
                try {
                    $result = $net->query('https://api2.nrg-tk.ru/v2/search/city?' . http_build_query(['zipCode' => $settings['sender_zip']]));
                    $settings['sender_city_code'] = ifempty($result['city']['id'], '');
                    $settings['sender_city_name'] = ifempty($result['city']['name']);
                } catch (waException $e) {
                    throw new waException('Не удалось определить город отправителя по почтовому индексу');
                }
            }

        }
        return parent::saveSettings($settings);
    }


    /**
     *
     */
    protected function calculate()
    {
        if (empty($this->sender_city_code)) {
            return 'Расчет стоимости доставки невозможен';
        }

        $zip = mb_ereg_replace('\D', '', $this->getAddress('zip'));
        if (empty($zip)) {
            return array(array('rate' => null, 'comment' => 'Не указан почтовый индекс города доставки'));
        }
        if (mb_strlen($zip) != 6) {
            return array(array('rate' => null, 'comment' => 'Неправильный почтовый индекс города доставки'));
        }

        $net = new waNet(array('format' => waNet::FORMAT_JSON, 'verify' => false));
        try {
            $target_city = $net->query('https://api2.nrg-tk.ru/v2/search/city?' . http_build_query(['zipCode' => $zip]));
        } catch (waException $e) {
            return array(array('rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна'));
        }

        $warehouses = $this->getWarehouses($target_city['city']['id']);

        $request = array(
            'idCityFrom' => intval($this->sender_city_code),
            'idCityTo'   => intval($target_city['city']['id']),
            'cover'      => 0,
            'idCurrency' => 1,
            'items'      => array(
                array(
                    'weight' => $this->getTotalWeight(),
                    'width'  => 0.1,
                    'height' => 0.1,
                    'length' => 0.1
                )
            )
        );

        try {
            $result = $net->query('https://api2.nrg-tk.ru/v2/price', $request, waNet::METHOD_POST);
            if (empty($result['transfer'])) {
                throw new waException();
            }
        } catch (waException $e) {
            return array(array('rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна'));
        }

        $todoor = array();
        $ware = array();

        /** @var float $pickup_price стоимость доставки груза до склада ТК */
        $pickup_price = $this->pickup_price == 'sender' ? ifempty($result['request']['price'], 0) : 0;
        $pickup_price = floatval(str_replace(',', '.', $pickup_price));

        /** Оптимизатор "самый дешевый". Отсортируем по цене и оставим только первый */
        if ($this->optimize == 'cheapest') {
            usort($result['transfer'], function ($a, $b) {
                if ($a['price'] == $b['price']) {
                    return 0;
                }
                return $a['price'] > $b['price'] ? 1 : -1;
            });
            array_splice($result['transfer'], 1);
        }

        if (ifempty($result['delivery'], array()) && ($this->delivery_type != 'tostore')) {
            foreach ($result['transfer'] as $variant) {
                $todoor['TODOOR-' . $variant['typeId']] = array(
                    'rate'         => $variant['price'] + $result['delivery']['price'] + $pickup_price,
                    'currency'     => 'RUB',
                    'name'         => $variant['type'] . '+до двери',
                    'comment'      => $variant['type'] . '-доставка и экспедирование по городу до адреса',
                    'est_delivery' => $variant['interval']
                );
            }
        }

        if ($this->delivery_type != 'todoor') {
            foreach ($warehouses as $w) {
                foreach ($result['transfer'] as $t) {
                    $ware['WRH-' . $w['id'] . '-' . $t['typeId']] = array(
                        'name'         => $w['title'] . ' / ' . $t['type'],
                        'rate'         => $t['price'] + $pickup_price,
                        'currency'     => 'RUB',
                        'comment'      => $w['address'] . '; ' . $w['phone'],
                        'est_delivery' => $t['interval']
                    );
                }
            }
        }

        $rates = $this->show_first == 'todoor' ? $todoor + $ware : $ware + $todoor;

        return $rates ? $rates : array(array('rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна'));

        /*
                 * array (
          'places' => 1,
          'weight' => 0.10000000000000001,
          'volume' => 0.001,
          'cover' => 0,
          'transfer' =>
          array (
            0 =>
            array (
              'typeId' => 1,
              'type' => 'Авто',
              'price' => 280,
              'interval' => '5-8 дней',
              'oversize' => NULL,
            ),
            1 =>
            array (
              'typeId' => 3,
              'type' => 'ЖД',
              'price' => 300,
              'interval' => '4-6 дней',
              'oversize' => NULL,
            ),
            2 =>
            array (
              'typeId' => 2,
              'type' => 'АВИА',
              'price' => 550,
              'interval' => '4-5 дней',
              'oversize' => NULL,
            ),
          ),
          'request' =>
          array (
            'typeId' => 0,
            'type' => '',
            'price' => 400,
            'interval' => '',
            'oversize' => NULL,
          ),
          'delivery' =>
          array (
            'typeId' => 0,
            'type' => '',
            'price' => 230,
            'interval' => '',
            'oversize' => NULL,
          ),
        )
                 *
                 */
    }

    /**
     * @return float
     */
    protected function getTotalWeight()
    {
        $weight = floatval(str_replace(',', '.', parent::getTotalWeight()));

        return $weight > 0 ? $weight : 0.1;
    }

    protected function getWarehouses($city_id)
    {
        $cache = wa()->getCache('default', 'webasyst');
        if (!$cache) $cache = new waCache(new waFileCacheAdapter(array()), 'webasyst');

        $cities = $cache->get('cities', 'nrg');
        if (!$cities) {
            $net = new waNet(array('format' => waNet::FORMAT_JSON, 'verify' => false));
            try {
                $cities = $net->query('https://api2.nrg-tk.ru/v2/cities?lang=ru');
                $cache->set('cities', $cities, 21600, 'nrg');
            } catch (waException $e) {
                $cities = [];
            }
        }

        $city = array();
        foreach ($cities['cityList'] as $c) {
            if ($c['id'] == $city_id) {
                $city = $c;
                break;
            }
        }
        if (empty($city)) {
            return array();
        }

        return ifempty($city['warehouses'], array());
    }
}