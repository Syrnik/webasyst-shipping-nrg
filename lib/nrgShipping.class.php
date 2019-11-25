<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2015-2019
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

use SergeR\CakeUtility\Hash;
use Syrnik\nrgShipping\EstimatedDelivery;
use Syrnik\WaShippingUtils;
use SergeR\Util\EvalMath;

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
 */
class nrgShipping extends waShipping
{
    private $config;

    public function allowedAddress()
    {
        return array(array('country' => 'rus'));
    }

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

    /**
     * @return string
     */
    public function allowedLinearUnit()
    {
        return 'm';
    }

    public function getSettingsHTML($params = array())
    {
        $view = wa()->getView();
        if (!version_compare(PHP_VERSION, '5.6.0', '>=')) {
            $view->assign('errors', array(
                sprintf('Критическая ошибка. Требуется версия PHP 5.6.0 или старше. Сейчас используется %s. Работа плагина невозможна', PHP_VERSION)
            ));
            return $view->fetch($this->path . '/templates/settings.html');
        }

        $this->initControls();

        $default = array(
            'instance'            => & $this,
            'title_wrapper'       => '%s',
            'description_wrapper' => '<br><span class="hint">%s</span>',
            'translate'           => array(&$this, '_w'),
            'control_wrapper'     =>
                '<div class="field"><div class="name">%s</div><div class="value">%s%s</div></div>',
            'control_separator'   => '</div><div class="value">',
        );

        $options = (array)Hash::get($params, 'options');
        unset($params['options']);
        $params = array_merge($default, $params);

        foreach ($this->config() as $name => $row) {
            $row = array_merge($row, $params);
            $row['value'] = $this->getSettings($name);
            if (isset($options[$name])) {
                $row['options'] = $options[$name];
            }
            if (isset($params['value']) && isset($params['value'][$name])) {
                $row['value'] = $params['value'][$name];
            }
            if (!empty($row['control_type'])) {

                $tab = Hash::get($row, 'subject');
                if ($tab) {
                    $controls[$tab][$name] = waHtmlControl::getControl($row['control_type'], $name, $row);
                }
            }
        }

        $info = $this->info($this->id);

        $view = wa()->getView();
        $view->assign(compact('controls', 'info'));

        return $view->fetch($this->path . '/templates/settings.html');
    }

    /**
     * @param array $address
     * @return bool
     */
    public function isAllowedAddress($address = array())
    {
        $allowed = parent::isAllowedAddress($address);

        if (($this->city_hide == 'never') || !$allowed) {
            return $allowed;
        }

        if (empty($address)) {
            $address = $this->getAddress();
        }

        // название города отправителя == названию города получателя.
        $city_name = WaShippingUtils::replaceYo(WaShippingUtils::mb_trim(mb_strtolower(Hash::get($address, 'city'))));
        $my_city = WaShippingUtils::replaceYo(WaShippingUtils::mb_trim(mb_strtolower($this->sender_city_name)));
        if ($city_name == $my_city) {
            return false;
        }

        // индекс должен быть 6 цифр
        $zip = mb_ereg_replace('\D', '', Hash::get($address, 'zip', ''));
        if (strlen($zip) != 6) {
            return $allowed;
        }

        $net = new waNet(array('format' => waNet::FORMAT_JSON, 'verify' => false));
        try {
            $target_city = $net->query('https://api2.nrg-tk.ru/v2/search/city?' . http_build_query(['zipCode' => $zip]));
        } catch (waException $e) {
            if ($this->city_hide == 'always') {
                return false;
            }
            return $allowed;
        }

        $my_city_code = Hash::get($target_city, 'city.id');
        // неизвестный город
        if (!$my_city_code && ($this->city_hide == 'always')) {
            return false;
        }

        return $my_city_code != $this->sender_city_code;
    }

    public function requestedAddressFields()
    {
        return array(
            'country' => ['cost' => true, 'required' => true],
            'zip'     => ['cost' => true, 'required' => true]
        );
    }

    public function saveSettings($settings = array())
    {
        if (array_key_exists('sender_zip', $settings)) {
            $settings['sender_city_code'] = '';
            if (!empty($settings['sender_zip'])) {
                $net = new waNet(array('format' => waNet::FORMAT_JSON, 'verify' => false));
                try {
                    $result = $net->query('https://api2.nrg-tk.ru/v2/search/city?' . http_build_query(['zipCode' => $settings['sender_zip']]));
                    $settings['sender_city_code'] = Hash::get($result, 'city.id', '');
                    $settings['sender_city_name'] = Hash::get($result, 'city.name', '');
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
     */
    public function settingPackageSelect($name, $params = array())
    {
        foreach ($params as $field => $param) {
            if (strpos($field, 'wrapper')) {
                unset($params[$field]);
            }
        }
        if (!empty($params['value']) && !is_array($params['value'])) {
            $params['value'] = array(array('min_weight' => 0, 'package' => $params['value']));
        }

        if (empty($params['value'])) {
            $params['value'] = array(array('min_weight' => 0, 'package' => '20x20x20'));
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

        $control = $view->fetch(
            waConfig::get('wa_path_plugins') . '/shipping/nrg/templates/controls/package_select.html'
        );

        return $control;
    }

    public function tracking($tracking_id = null)
    {
        return 'Отследить сосояние доставки по номеру накладной на сайте ТК Энергия <a href="https://nrg-tk.ru/client/tracking/">https://nrg-tk.ru/client/tracking/</a>';
    }

    /**
     *
     */
    protected function calculate()
    {
        if (!version_compare(PHP_VERSION, '5.6.0', '>=')) {
            return 'Расчет стоимости доставки невозможен';
        }

        if (empty($this->sender_city_code)) {
            return 'Расчет стоимости доставки невозможен';
        }

        if ($this->getAddress('country') !== 'rus') {
            return array(['rate' => null, 'comment' => 'Расчет стоимости может быть выполнен только для доставки по России']);
        }

        $zip = mb_ereg_replace('\D', '', $this->getAddress('zip'));
        if (empty($zip)) {
            return array(['rate' => null, 'comment' => 'Не указан почтовый индекс города доставки']);
        }
        if (mb_strlen($zip) != 6) {
            return array(['rate' => null, 'comment' => 'Неправильный почтовый индекс города доставки']);
        }

        if (($this->zero_weight_item == 'stop') && $this->hasZeroWeightItems()) {
            $msg = mb_ereg_replace('^[[:space:]]*([\s\S]*?)[[:space:]]*$', '\1', $this->zero_weight_item_msg);
            return empty($msg) ? 'Недоступно' : $msg;
        }

        $net = new waNet(['format' => waNet::FORMAT_JSON, 'verify' => false]);
        try {
            $target_city = $net->query('https://api2.nrg-tk.ru/v2/search/city?' . http_build_query(['zipCode' => $zip]));
        } catch (waException $e) {
            return array(['rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна']);
        }

        $warehouses = $this->getWarehouses($target_city['city']['id']);

        try {
            $dimensions = $this->getTotalSize();
        } catch (waException $e) {
            return array(array('rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна'));
        }

        $request = array(
            'idCityFrom' => intval($this->sender_city_code),
            'idCityTo'   => intval($target_city['city']['id']),
            'cover'      => 0,
            'idCurrency' => 1,
            'items'      => array(array('weight' => $this->getTotalWeight()) + $dimensions)
        );

        try {
            /** @var array $result */
            $result = $net->query('https://api2.nrg-tk.ru/v2/price', $request, waNet::METHOD_POST);
            if (empty($result['transfer'])) {
                throw new waException();
            }
        } catch (waException $e) {
            return array(['rate' => null, 'comment' => 'Доставка в город с указанным почтовым индексом невозможна']);
        }

        $todoor = array();
        $ware = array();

        /** @var float $pickup_price стоимость доставки груза до склада ТК */
        $pickup_price = $this->pickup_price == 'sender' ? Hash::get($result, 'request.price', 0) : 0;
        $pickup_price = WaShippingUtils::strToFloat($pickup_price);

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

        $estimated_delivery = (new EstimatedDelivery())->setDepartureString($this->getPackageProperty('departure_datetime'));

        // Варианты доставки "до двери". Магистральный тариф плюс стоимость трансфера по городу плюс стоимость забора от отправителя
        if (Hash::get($result, 'delivery') && ($this->delivery_type != 'tostore')) {
            foreach ($result['transfer'] as $variant) {
                $id = 'TODOOR-' . $variant['typeId'];

                $todoor[$id] = array(
                    'rate'     => $this->calcTotalCost($variant['price'] + $result['delivery']['price'] + $pickup_price),
                    'currency' => 'RUB',
                    'name'     => $variant['type'] . '+до двери',
                    'comment'  => $variant['type'] . '-доставка и экспедирование по городу до адреса',
                    'type'     => waShipping::TYPE_TODOOR
                );

                try {
                    $estimated_delivery->parseRegexRange((string)Hash::get($variant, 'interval'));
                    $todoor[$id]['est_delivery'] = $estimated_delivery->getWebasystEstDelivery();
                    $todoor[$id]['delivery_date'] = $estimated_delivery->getWebasystDeliveryDates();
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
                        'rate'        => $this->calcTotalCost($t['price'] + $pickup_price),
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
                        $estimated_delivery->parseRegexRange((string)Hash::get($t, 'interval'));
                        $ware[$id]['est_delivery'] = $estimated_delivery->getWebasystEstDelivery();
                        $ware[$id]['delivery_date'] = $estimated_delivery->getWebasystDeliveryDates();
                    } catch (Exception $e) {
                        //todo log
                    }
                }
            }
        }

        // Что показывать в первую очередь
        $rates = $this->show_first == 'todoor' ? $todoor + $ware : $ware + $todoor;

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

        return (array)Hash::get($city, 'warehouses');
    }

    protected function hasZeroWeightItems()
    {
        $items = $this->getItems();
        $zero_weighted = array_filter($items, function ($item) {
            return !array_key_exists('weight', $item) || (WaShippingUtils::strToFloat($item['weight']) == 0);
        });

        return count($zero_weighted) > 0;
    }

    /**
     * @see waShipping::init()
     */
    protected function init()
    {
        require_once 'vendors/autoload.php';
        parent::init();
        waAutoload::getInstance()->add([
            'Syrnik\\nrgShipping\\EstimatedDelivery' => "wa-plugins/shipping/nrg/lib/classes/EstimatedDelivery.class.php"
        ]);
    }

    protected function initControls()
    {
        $this->registerControl('PackageSelect', [$this, 'settingPackageSelect']);
        parent::initControls();
    }

    /**
     * Расчет наценки
     *
     * @param float|string $nrg_cost
     * @return float
     */
    private function calcTotalCost($nrg_cost)
    {
        $nrg_cost = WaShippingUtils::strToFloat($nrg_cost);
        $percent_sign_pos = strpos($this->handling_cost, '%');

        // Если процентов нет, то и думать нечего. Приплюсуем и все дела
        if (($percent_sign_pos === false) && ($this->handling_base != 'formula')) {
            return round(WaShippingUtils::strToFloat($this->handling_cost) + $nrg_cost, 2);
        }

        if ($this->handling_base == 'formula') {
            $EvalMath = new EvalMath\EvalMath();

            try {
                $EvalMath->evaluate('z=' . str_replace(',', '.', (string)$this->getTotalPrice()));
                $EvalMath->evaluate('s=' . str_replace(',', '.', (string)$nrg_cost));
                $math_result = $EvalMath->evaluate($this->handling_cost);
            } catch (EvalMath\Exception\AbstractEvalMathException $e) {
                self::_log('Ошибка исполнения формулы "' . $this->handling_cost . '" (' . $e->getMessage() . ')');
                return round($nrg_cost, 2);
            }

            return round($math_result, 2);
        }

        switch ($this->handling_base) {
            case 'shipping' :
                $base = $nrg_cost;
                break;
            case 'order_shipping':
                $base = $this->getTotalPrice() + $nrg_cost;
                break;
            case 'order':
            default:
                $base = $this->getTotalPrice();
        }

        $cost = substr($this->handling_cost, 0, $percent_sign_pos);
        if (strlen($cost) < 1) {
            return $nrg_cost;
        }

        return round($nrg_cost + $base * floatval($cost) / 100, 2);
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
     * @return array ['length'=>0.0, 'width'=>0.0, 'height'=>0.0]
     * @throws waException
     */
    protected function getTotalSize()
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
        $dimensions['length'] = (string)Hash::get($_dimensions, '0', '20');
        $dimensions['width'] = (string)Hash::get($_dimensions, '0', '20');
        $dimensions['height'] = (string)Hash::get($_dimensions, '0', '20');

        foreach ($dimensions as $key => $item) {
            $dimensions[$key] = WaShippingUtils::strToFloat($item);
            if (!$dimensions[$key]) {
                $dimensions[$key] = 20;
            }

            $dimensions[$key] = $dimensions[$key] / 100;

        }

        return $dimensions;
    }

    private static function _log($msg, $critical = false)
    {
        if (waSystemConfig::isDebug() || $critical) {
            waLog::log($msg, 'shipping/nrg.log');
        }
    }

    /**
     * @return string
     * @throws waException
     */
    private function getAppDimensionSupport()
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
}