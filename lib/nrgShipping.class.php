<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2015-2025
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

use Syrnik\WaShippingUtils;

/**
 * @property string $delivery_type
 * @property string $optimize
 * @property string $pickup_price
 * @property string $show_first
 * @property string $sender_city_code
 * @property array $standard_parcel_dimensions
 * @property string $zero_weight_item
 * @property string $zero_weight_item_msg
 *
 * @property string $sender_city_name
 *
 * @property string $handling_base
 * @property string $handling_cost
 *
 * @property string $city_hide
 * @property-read string $free_delivery_door
 * @property-read string $free_delivery_terminal
 */
class nrgShipping extends waShipping
{
    /**
     * @var
     */
    private $config;

    /**
     * @return array
     */
    public function allowedAddress(): array
    {
        return [
            ['country' => 'rus']
        ];
    }

    /**
     *
     * @return string ISO3 currency code or array of ISO3 codes
     */
    public function allowedCurrency(): string
    {
        return 'RUB';
    }

    /**
     *
     * @return string Weight units or array of weight units
     */
    public function allowedWeightUnit(): string
    {
        return 'kg';
    }

    /**
     * @return string
     */
    public function allowedLinearUnit(): string
    {
        return 'm';
    }

    /**
     * @param array $params
     * @return string
     * @throws SmartyException
     * @throws waException
     * @throws Exception
     */
    public function getSettingsHTML($params = []): string
    {
        $template = version_compare(wa()->whichUI(), '2.0', '>=')
            ? 'settings.html'
            : 'settings-legacy.html';

        $this->initControls();
        $errors = [];

        $default = array(
            'instance'            => & $this,
            'title_wrapper'       => '%s',
            'description_wrapper' => '<br><span class="hint">%s</span>',
            'translate'           => array(&$this, '_w'),
            'control_wrapper'     =>
                '<div class="field"><div class="name">%s</div><div class="value">%s%s</div></div>',
            'control_separator'   => '</div><div class="value">',
        );

        $options = (array)($params['options'] ?? []);
        unset($params['options']);
        $params = array_merge($default, $params);
        $controls = [];

        foreach ($this->config() as $name => $row) {
            $row = array_merge($row, $params);
            $row['value'] = $this->getSettings($name);
            if (isset($options[$name])) {
                $row['options'] = $options[$name];
            }
            if (isset($params['value'][$name])) {
                $row['value'] = $params['value'][$name];
            }

            if (!empty($row['control_type'])) {
                $tab = $row['subject'] ?? '';
                if ($tab) {
                    $controls[$tab][$name] = waHtmlControl::getControl($row['control_type'], $name, $row);
                }
            }
        }

        $info = $this->info($this->id);
        $urls = [
            'search_city' => $this->getInteractionUrl('cityByZip'),
        ];

        $view = wa()->getView();
        $view->assign(compact('controls', 'info', 'urls', 'errors'));

        return $view->fetch($this->path . "/templates/$template");
    }

    /**
     * @param array $address
     * @return bool
     */
    public function isAllowedAddress($address = []): bool
    {
        $allowed = parent::isAllowedAddress($address);

        if ($this->city_hide === 'never' || !$allowed) {
            return $allowed;
        }

        if (empty($address)) {
            $address = $this->getAddress();
        }

        // название города отправителя == названию города получателя.
        $city_name = WaShippingUtils::replaceYo(WaShippingUtils::mb_trim(mb_strtolower((string)($address['city'] ?? ''))));
        $my_city = WaShippingUtils::replaceYo(WaShippingUtils::mb_trim(mb_strtolower($this->sender_city_name)));
        if ($city_name == $my_city) {
            return false;
        }

        // индекс должен быть 6 цифр
        $zip = preg_replace('\D', '', (string)($address['zip'] ?? ''));
        if (strlen($zip) !== 6) {
            return $allowed;
        }

        try {
            $target_city = $this->getEnergyAPI()->search_city($zip);
            if (isset($target_city['error'])) {
                throw new waException($target_city['error']['message'] ?? 'Доставка в город с указанным почтовым индексом невозможна');
            }
        } catch (waException $e) {
            if ($this->city_hide == 'always') {
                return false;
            }

            return $allowed;
        }

        $my_city_code = $target_city['id'] ?? null;
        // неизвестный город
        if (empty($my_city_code) && $this->city_hide === 'always') {
            return false;
        }

        return (int)$my_city_code !== (int)$this->sender_city_code;
    }

    /**
     * @return array
     */
    public function requestedAddressFields(): array
    {
        return array(
            'country' => ['cost' => true, 'required' => true],
            'zip'     => ['cost' => true, 'required' => true]
        );
    }

    /**
     * @param array $settings
     * @return array
     * @throws waException
     */
    public function saveSettings($settings = []): array
    {
        if (array_key_exists('sender_zip', $settings)) {
            $settings['sender_city_code'] = '';
            $settings['sender_zip'] = trim($settings['sender_zip'] ?? '');
            if (!empty($settings['sender_zip'])) {
                try {
                    $result = $this->getEnergyAPI()->search_city($settings['sender_zip']);
                    if (isset($result['error'])) {
                        throw new waException($result['error']['message']);
                    }
                    $settings['sender_city_code'] = $result['city']['id'] ?? '';
                    $settings['sender_city_name'] = $result['city']['name'] ?? '';
                } catch (waException $e) {
                    throw new waException('Не удалось определить город отправителя по почтовому индексу');
                }
            }
        }

        return parent::saveSettings($settings);
    }

    /**
     * @param $name
     * @param array $params
     * @return string
     * @throws SmartyException
     * @throws waException
     */
    public function settingPackageSelect($name, array $params = []): string
    {
        foreach ($params as $field => $param) {
            if (strpos($field, 'wrapper')) {
                unset($params[$field]);
            }
        }
        if (!empty($params['value']) && !is_array($params['value'])) {
            $params['value'] = [['min_weight' => 0, 'package' => $params['value']]];
        }

        if (empty($params['value'])) {
            $params['value'] = [['min_weight' => 0, 'package' => '20x20x20']];
        }

        waHtmlControl::addNamespace($params, $name);

        $namespace = '';
        if (!empty($params['namespace'])) {
            if (is_array($params['namespace'])) {
                $namespace = array_shift($params['namespace']);
                while (($namespace_chunk = array_shift($params['namespace'])) !== null) {
                    $namespace .= "[{$namespace_chunk}]";
                }
            } else {
                $namespace = $params['namespace'];
            }
        }

        try {
            $external_calc_support = $this->getAppDimensionSupport() === 'supported';
        } catch (waException $e) {
            $external_calc_support = false;
        }

        $view = wa()->getView();
        $view->assign(compact('namespace', 'params', 'external_calc_support'));

        return $view->fetch(
            waConfig::get('wa_path_plugins') . '/shipping/nrg/templates/controls/package_select.html'
        );
    }

    /**
     * @param null $tracking_id
     * @return string
     */
    public function tracking($tracking_id = null)
    {
        return 'Отследить состояние доставки по номеру накладной на сайте ТК Энергия <a href="https://nrg-tk.ru/client/tracking/">https://nrg-tk.ru/client/tracking/</a>';
    }

    /**
     * @return array|false|string
     * @throws waException
     */
    protected function calculate()
    {
        if (empty($this->sender_city_code)) {
            return 'Расчет стоимости доставки невозможен';
        }

        if ($this->getAddress('country') !== 'rus') {
            return [['rate' => null, 'comment' => 'Расчет стоимости может быть выполнен только для доставки по России']];
        }

        $zip = preg_replace('\D', '', $this->getAddress('zip'));
        if (empty($zip)) {
            return array(['rate' => null, 'comment' => 'Не указан почтовый индекс города доставки']);
        }
        if (mb_strlen($zip) != 6) {
            return [['rate' => null, 'comment' => 'Неправильный почтовый индекс города доставки']];
        }

        if ($this->zero_weight_item == 'stop' && $this->hasZeroWeightItems()) {
            $msg = preg_replace('^[[:space:]]*([\s\S]*?)[[:space:]]*$', '\1', $this->zero_weight_item_msg);
            return empty($msg) ? 'Недоступно' : $msg;
        }

        try {
            $target_city = $this->getEnergyAPI()->search_city($zip);
            if (isset($target_city['error'])) {
                throw new waException('Доставка в город с указанным почтовым индексом невозможна');
            }
        } catch (waException $e) {
            return [['rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна']];
        }

        $warehouses = $this->getWarehouses($target_city['city']['id']);

        try {
            $dimensions = $this->getTotalSize();
        } catch (waException $e) {
            return [['rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна']];
        }

        $request = [
            'idCityFrom' => intval($this->sender_city_code),
            'idCityTo'   => intval($target_city['city']['id']),
            'cover'      => 0,
            'idCurrency' => 1,
            'places'     => 1,
            'items'      => [['weight' => $this->getTotalWeight()] + $dimensions]
        ];

        try {
            $result = $this->getEnergyAPI()->price($request);
            if (isset($result['error'])) {
                throw new waException($result['error']['message'] ?? 'Ошибка');
            }
            if (empty($result['transfer'])) {
                throw new waException();
            }
        } catch (waException $e) {
            return [['rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна']];
        }

        $to_door = [];
        $ware = [];

        /** @var float $pickup_price стоимость доставки груза до склада ТК */
        $pickup_price = $this->pickup_price == 'sender' ? ($result['request']['price'] ?? 0) : 0;
        $pickup_price = WaShippingUtils::toFloat($pickup_price);

        /** Оптимизатор "самый дешевый". Отсортируем по цене и оставим только первый */
        if ($this->optimize == 'cheapest') {
            usort($result['transfer'], fn($a, $b) => $a['price'] <=> $b['price']);
            array_splice($result['transfer'], 1);
        }

        $estimated_delivery = (new nrgShippingEstimatedDelivery())->setDepartureString($this->getPackageProperty('departure_datetime'));

        // Варианты доставки "до двери". Магистральный тариф плюс стоимость трансфера по городу плюс стоимость забора от отправителя
        if ($result['delivery'] && $this->delivery_type != 'tostore') {
            foreach ($result['transfer'] as $variant) {
                $id = 'TODOOR-' . $variant['typeId'];

                $to_door[$id] = array(
                    'rate'     => $this->calcTotalCost($variant['price'] + $result['delivery']['price'] + $pickup_price, $this->free_delivery_door),
                    'currency' => 'RUB',
                    'name'     => $variant['type'] . '+до двери',
                    'comment'  => $variant['type'] . '-доставка и экспедирование по городу до адреса',
                    'type'     => waShipping::TYPE_TODOOR
                );

                try {
                    $estimated_delivery->parseRegexRange((string)($variant['interval'] ?? ''));
                    $to_door[$id]['est_delivery'] = $estimated_delivery->getWebasystEstDelivery();
                    $to_door[$id]['delivery_date'] = $estimated_delivery->getWebasystDeliveryDates();
                } catch (Exception $e) {
                    //todo log
                }
            }
        }

        // Варианты доставки до терминала Энергии. Просто магистральный тариф плюс стоимость забора от отправителя
        if ($this->delivery_type != 'todoor') {
            foreach ($warehouses as $w) {
                foreach ($result['transfer'] as $t) {
                    $id = 'WRH-' . $w['id'] . '-' . $t['typeId'];
                    $ware[$id] = array(
                        'name'        => $w['title'] . ' / ' . $t['type'],
                        'rate'        => $this->calcTotalCost($t['price'] + $pickup_price, $this->free_delivery_terminal),
                        'currency'    => 'RUB',
                        'comment'     => $w['address'] . '; ' . $w['phone'],
                        'type'        => waShipping::TYPE_PICKUP,
                        'custom_data' => ['pickup' => [
                            'id'          => $id,
                            'lat'         => $w['latitude'],
                            'lng'         => $w['longitude'],
                            'name'        => $w['title'] . ' (' . $t['type'] . ')',
                            'description' => $w['address']
                        ]]
                    );

                    try {
                        $estimated_delivery->parseRegexRange((string)($t['interval'] ?? ''));
                        $ware[$id]['est_delivery'] = $estimated_delivery->getWebasystEstDelivery();
                        $ware[$id]['delivery_date'] = $estimated_delivery->getWebasystDeliveryDates();
                    } catch (Exception $e) {
                        //todo log
                    }
                }
            }
        }

        // Что показывать в первую очередь
        $rates = $this->show_first == 'todoor' ? $to_door + $ware : $ware + $to_door;

        return $rates ? $rates : array(array('rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна'));
    }

    /**
     * @return float
     */
    protected function getTotalWeight()
    {
        $weight = WaShippingUtils::strToFloat(parent::getTotalWeight());

        return $weight > 0 ? $weight : 0.1;
    }

    /**
     * @param $city_id
     * @return array
     * @throws waException
     */
    protected function getWarehouses($city_id): array
    {
        $cache = wa()->getCache('default', 'webasyst');
        if (empty($cache)) {
            $cache = new waCache(new waFileCacheAdapter([]), 'webasyst');
        }

        $cities = $cache->get('cities', 'nrg');
        if (empty($cities)) {
            $net = new waNet(array('format' => waNet::FORMAT_JSON, 'verify' => false));
            try {
                $cities = $this->getEnergyAPI()->cities();
                if (isset($cities['error'])) {
                    throw new waException($cities['error']['message']);
                }
                $cache->set('cities', $cities, 21600, 'nrg');
            } catch (waException $e) {
                $cities = [];
            }
        }

        $city = [];
        foreach ($cities['cityList'] as $c) {
            if ($c['id'] == $city_id) {
                $city = $c;
                break;
            }
        }
        if (empty($city)) {
            return [];
        }

        return (array)($city['warehouses'] ?? []);
    }

    /**
     * @return bool
     */
    protected function hasZeroWeightItems(): bool
    {
        $items = $this->getItems();
        $zero_weighted = array_filter($items, fn($item) => !array_key_exists('weight', $item) || WaShippingUtils::toFloat($item['weight']) === 0);

        return !empty($zero_weighted);
    }

    /**
     * @see waShipping::init()
     */
    protected function init()
    {
        parent::init();
        require_once 'vendors/autoload.php';
    }

    /**
     * @throws Exception
     */
    protected function initControls()
    {
        $this->registerControl('PackageSelect', [$this, 'settingPackageSelect']);
        parent::initControls();
    }

    /**
     * Расчет наценки
     *
     * @param float|string $nrg_cost
     * @param string $free_delivery
     * @return float
     * @deprecated
     */
    private function calcTotalCost($nrg_cost, $free_delivery = ''): float
    {
        $handling_base = $this->handling_base;
        if (!trim($this->handling_cost) && $this->handling_base == 'formula') {
            $handling_base = 'order';
        }

        return max(0, WaShippingUtils::calcTotalCost(
            $nrg_cost,
            $this->getTotalPrice(),
            $this->getTotalRawPrice(),
            strtolower($this->handling_cost),
            (string)$handling_base,
            (string)$free_delivery
        ));
    }

    /**
     * Чтение настроек из settings.php
     *
     */
    private function config()
    {
        if ($this->config === null) {
            $path = $this->path . '/lib/config/settings.php';
            if (file_exists($path)) {
                $this->config = include($path);

                foreach ($this->config as & $config) {
                    if (isset($config['title'])) {
                        $config['title'] = $this->_w($config['title']);
                    }
                    if (isset($config['description'])) {
                        $config['description'] = $this->_w($config['description']);
                    }
                }
                unset($config);
            }
            if (!is_array($this->config)) {
                $this->config = array();
            }
        }
        return $this->config;
    }

    /**
     * Превращаем строку из настроек размеров посылки "ДxШxВ" в массив
     * [
     *  'length'=>float,
     *  'width' => float,
     *  'height' => float
     * ]
     * @return array{length:float, width:float, height:float}
     * @throws waException
     */
    protected function getTotalSize(): array
    {
        if ($this->getAppDimensionSupport() === 'supported') {
            $dimensions = parent::getTotalSize();
            if (!is_array($dimensions)) {
                throw new waException('Ошибочные размеры упаковки');
            }

            return $dimensions;
        }

        if (!is_array($this->standard_parcel_dimensions)) {
            $this->standard_parcel_dimensions = array(
                array('min_weight' => 0, 'package' => $this->standard_parcel_dimensions)
            );
        }

        $weight = $this->getTotalWeight();
        $package = null;

        foreach ($this->standard_parcel_dimensions as $rule) {
            $min_weight = WaShippingUtils::strToFloat($rule['min_weight']);
            if ($weight < $min_weight) {
                break;
            }
            $package = $rule['package'];
        }

        if (is_null($package)) {
            throw new waException(sprintf("Не найдено подходящего размера упаковки для веса заказа %.3f кг.", $weight));
        }

        $_dimensions = (array)preg_split('/[хx*]/i', $package);
        $dimensions['length'] = (string)($_dimensions[0] ?? '20');
        $dimensions['width'] = (string)($_dimensions[1] ?? '20');
        $dimensions['height'] = (string)($_dimensions[2] ?? '20');

        foreach ($dimensions as $key => $item) {
            $dimensions[$key] = WaShippingUtils::strToFloat($item);
            if (!$dimensions[$key]) {
                $dimensions[$key] = 20;
            }

            $dimensions[$key] = $dimensions[$key] / 100;

        }

        return $dimensions;
    }

    /**
     * @return string
     * @throws waException
     */
    private function getAppDimensionSupport(): string
    {
        $dims = $this->getAdapter()->getAppProperties('dimensions');

        if ($dims === null) {
            return 'not_supported';
        } elseif ($dims === false) {
            return 'not_set';
        } elseif ($dims === true) {
            return 'no_external';
        }
        return 'supported';
    }

    /**
     * Хак, чтобы json-контроллерам тоже достался экземпляр плагина
     * А если указан ключ конкретной конфигурации, так чтоб не просто экземпляр, а экземпляр указанной конфигурации
     *
     * @param string $module
     * @param string $action
     * @return waController|waJsonActions|waJsonController|waSystemPluginAction|waSystemPluginActions
     * @throws waException
     */
    public function getController($module = 'backend', $action = 'Default')
    {
        $controller = parent::getController($module, $action);
        if (method_exists($controller, 'setPlugin')) {
            $key = waRequest::get('plugin_key');
            $controller->setPlugin($key ? waShipping::factory($this->id, $key, $this->app_id) : $this);
        }

        return $controller;
    }

    /**
     * @return nrgShippingEnergyAPI
     */
    public function getEnergyAPI(): nrgShippingEnergyAPI
    {
        return new nrgShippingEnergyAPI();
    }
}
