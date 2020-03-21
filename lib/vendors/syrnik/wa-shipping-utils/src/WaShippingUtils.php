<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license Webasyst
 */

namespace Syrnik;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use SergeR\Util\EvalMath\Exception\AbstractEvalMathException;
use Syrnik\WaShippingUtils\CalcTotalCostException;
use SergeR\Util\EvalMath\EvalMath;

class WaShippingUtils
{
    public static function replaceYo($str)
    {
        return preg_replace(['/ё/u', '/Ё/u'], ['е', 'Е'], $str);
    }

    /**
     * Multibyte trim
     *
     * @param string $str
     * @return string
     */
    public static function mb_trim($str)
    {
        return preg_replace('/^[[:space:]]*([\s\S]*?)[[:space:]]*$/ui', '\1', $str);
    }

    /**
     * Расчет дней до отправки исходя из часа переноса отгрузки, дней на комплектацию
     * и дней недели, когда производится передача отправления в курьерскую службу
     *
     * 1. Учесть перенос часа
     * 2. Учесть комлектацию
     * 3. Учесть день недели
     *
     * $params['workdays'] - sting[], дополнительные рабочие дни, d.m.Y
     * $params['weekends'] - string[], дополнительные выходные дние, d.m.Y
     * $params['start_day'] - string|DateTimeInterface начальный день (если не указано - сегодня)
     *
     * @param int $limit_hour час переноса отгрузки
     * @param int $add_days добавить дней на комлектацию
     * @param array $weekdays массив дней недели, когда передаются заказы
     * @param array $params Дополнительный параметры
     * @return int
     * @throws Exception
     */
    public static function calcDaysToShip($limit_hour = 0, $add_days = 0, array $weekdays = ['1', '2', '3', '4', '5', '6', '7'], array $params = [])
    {
        $limit_hour = (($limit_hour > 0) && ($limit_hour < 24)) ? $limit_hour : 0;
        $one_day = new DateInterval('P1D');

        $day = new DateTime();

        if (isset($params['start_day'])) {
            $start_day = $params['start_day'];
            if ($start_day instanceof DateTimeInterface) {
                $day = new DateTime($start_day->format('Y-m-d H:i:s'));
            } elseif (is_string($start_day)) {
                $day = new DateTime($start_day);
            }
        }

        if ($limit_hour && date('H') >= $limit_hour) {
            $day->add($one_day);
        }

        if ($add_days) {
            $day->add(new DateInterval("P{$add_days}D"));
        }

        $workdays = isset($params['workdays']) ? (array)$params['workdays'] : [];
        $weekends = isset($params['weekends']) ? (array)$params['weekends'] : [];

        $workdays = array_filter(array_map(function ($v) {
            return DateTime::createFromFormat('d.m.Y', $v);
        }, $workdays));

        $weekends = array_filter(array_map(function ($v) {
            return DateTime::createFromFormat('d.m.Y', $v);
        }, $weekends));

        $isWorkday = function (DateTimeInterface $v) use ($workdays) {
            /** @var DateTimeInterface $workday */
            foreach ($workdays as $workday) {
                if ($v->format('Y-m-d') == $workday->format('Y-m-d')) {
                    return true;
                }
            }
            return false;
        };

        $isWeekend = function (DateTimeInterface $v) use ($weekends) {
            /** @var DateTimeInterface $weekend */
            foreach ($weekends as $weekend) {
                if ($v->format('Y-m-d') == $weekend->format('Y-m-d')) {
                    return true;
                }
            }
            return false;
        };

        for ($i = 0; $i < 365; $i++) {
            $dow = $day->format('N');
            if ((in_array($dow, $weekdays) && !$isWeekend($day)) || (!in_array($dow, $weekdays) && $isWorkday($day))) {
                break;
            }
            $day->add($one_day);
        }

        return (int)($day->diff(new DateTime, true)->format('%a'));
    }

    /**
     * Парсит строку с исключениями и вычисляет, запрещен ли город и/или регион
     * В строке должны быть города, города с регионами или регионы, разделенные точкой с запятой
     * (просто запятая -- это токсично, может быть что-нибудь типа "Зарайск, Зарайского р-на:37").
     * Регион должен начинаться с двоеточия<br>
     * <br>
     * Пример: москва,одинцово:50,:47<br><br>
     *
     *  - город в любом регионе называющийся "москва"<br>
     *  - город в 50 регионе, называющийся "одинцово"<br>
     *  - любой город в 47 регионе
     *
     * @param string $city_name
     * @param string $region_code
     * @param string $excluded
     * @return bool
     */
    public static function isBannedLocation($city_name, $region_code, $excluded)
    {
        // это у нас такой рантайм кэш типа
        static $_rules;

        $excluded = self::mb_trim($excluded);

        //настройка пустая, запрета нет
        if (!$excluded) {
            return false;
        }

        $key = md5($excluded);
        if (!is_array($_rules)) {
            $_rules = [];
        }

        if (isset($_rules[$key])) {
            $rules = $_rules[$key];
        } else {
            $rules = array_filter(array_map(function ($v) {
                return mb_strtolower(self::mb_trim($v));
            }, explode(';', $excluded)));

            // это странно, но после отсеивания пустых правил не оказалось. Это может случиться
            // если в строке были одна или несколько запятных и больше ничего
            if (empty($rules)) {
                return false;
            }

            $rules = array_map(function ($r) {
                if (mb_strpos($r, ':') === false) {
                    $r .= ':';
                }
                list($city, $region) = explode(':', $r, 2);
                return array(
                    'city_name'   => self::mb_trim($city),
                    'region_code' => self::mb_trim($region)
                );
            }, $rules);

            $_rules[$key] = $rules;
        }

        $city_name = self::mb_trim(self::replaceYo(mb_strtolower($city_name)));

        $banned = false;
        foreach ($rules as $rule) {
            //какой-то дебил сделал правило из одного двоеточия
            if (empty($rule['city_name']) && empty($rule['region_code'])) {
                continue;
            }
            if (empty($rule['region_code'])) {
                if ($city_name == $rule['city_name']) {
                    $banned = true;
                    break;
                }
                continue;
            }
            if (empty($rule['city_name'])) {
                if ($rule['region_code'] == $region_code) {
                    $banned = true;
                    break;
                }
                continue;
            }
            if (($city_name == $rule['city_name']) && ($region_code == $rule['region_code'])) {
                $banned = true;
                break;
            }
        }

        return $banned;
    }

    /**
     * Расчет наценки на исходную цену с учетом бесплатной доставки
     *
     * @param float $carrier_cost Сколько насчитал перевозчик
     * @param float $total_price Стоимость заказа с учетом скидок
     * @param float $total_raw_price Стоимость заказа без скидок
     * @param string $handling_cost Наценка
     * @param string $handling_base База для расчета наценки
     * @param string $free Порог бесплатной доствки
     * @return float
     * @throws CalcTotalCostException
     * @todo Надо вообще все перевести в формулы
     */
    public static function calcTotalCost($carrier_cost, $total_price = 0.0, $total_raw_price = 0.0, $handling_cost = '0', $handling_base = 'shipping', $free = '')
    {
        static $_cache;
        if (!is_array($_cache)) {
            $_cache = [];
        }

        $cache_key = md5(serialize(compact('carrier_cost', 'total_price', 'total_raw_price', 'handling_cost', 'handling_base', 'free')));
        if (isset($_cache[$cache_key])) {
            return $_cache[$cache_key];
        }

        $free = trim($free);
        if (strlen($free)) {
            $free = (float)str_replace(',', '.', $free);
            if ($total_price >= $free) {
                return 0.0;
            }
        }

        $percent_sign_pos = strpos($handling_cost, '%');

        // Если процентов нет, то и думать нечего. Приплюсуем и все дела
        if (($percent_sign_pos === false) && ($handling_base != 'formula')) {
            return round(floatval(str_replace(',', '.', $handling_cost)) + $carrier_cost, 2);
        }

        if ($handling_base == 'formula') {
            $EvalMath = new EvalMath;
            $handling_cost = strtolower($handling_cost);

            try {
                $EvalMath->evaluate('z=' . str_replace(',', '.', (string)$total_price));
                $EvalMath->evaluate('y=' . str_replace(',', '.', (string)$total_raw_price));
                $EvalMath->evaluate('s=' . str_replace(',', '.', (string)$carrier_cost));
                $math_result = $EvalMath->evaluate($handling_cost);
            } catch (AbstractEvalMathException $e) {
                throw (new CalcTotalCostException($e->getMessage()))->setFormula($handling_cost)->setFormulaVars($EvalMath->vars());
            }
            return max(0.0, round($math_result, 2));
        }

        switch ($handling_base) {
            case 'shipping' :
                $base = $carrier_cost;
                break;
            case 'order_shipping':
                $base = $total_price + $carrier_cost;
                break;
            case 'order':
            default:
                $base = $total_price;
        }

        $cost = substr($handling_cost, 0, $percent_sign_pos);
        if (strlen($cost) < 1) {
            return $carrier_cost;
        }

        $result = round($carrier_cost + $base * floatval(str_replace(',', '.', $cost)) / 100, 2);
        $_cache[$cache_key] = $result;

        return $result;
    }

    /**
     * Округление по заданным в настройках правилам
     *
     * @param float|string $price
     * @param string|float $rounding
     * @param string $rounding_type
     * @return float
     */
    public static function roundPrice($price, $rounding = '0.01', $rounding_type = 'std')
    {
        $price = static::strToFloat($price);
        $rounding = static::strToFloat($rounding);
        $precision = (int)(0 - log10($rounding));
        $rounded = round($price, $precision);

        if ($rounding_type == 'std') {
            return $rounded;
        }

        if (($rounding_type == 'up') && ($price > $rounded)) {
            return $rounded + $rounding;
        }

        if (($rounding_type == 'down') && ($rounded > $price)) {
            return $rounded - $rounding;
        }

        return $rounded;
    }

    /**
     * @param string $str
     * @return float
     */
    public static function strToFloat($str)
    {
        return (float)str_replace(',', '.', self::mb_trim($str));
    }

    /**
     * Возвращает строку с отформатированную как денежный формат (два знака после десятичного разделителя)
     *
     * @param float $value
     * @return string
     */
    public static function monetaryString($value)
    {
        return number_format($value, 2, '.', '');
    }

    /**
     * @param $value
     * @param array $castings
     * @return bool
     */
    public static function toBool($value, array $castings = ['true' => ['yes', 'да', 'on', 'вкл', 'true', 'истина'], 'false' => ['no', 'нет', 'off', 'выкл', 'false', 'ложь']])
    {
        if (is_string($value)) {
            $value = mb_strtolower($value, 'UTF-8');
        }
        if (in_array($value, $castings['true'], true)) {
            return true;
        }
        if (in_array($value, $castings['false'], true)) {
            return false;
        }

        return (bool)$value;
    }

    /**
     * @param string|array|\ArrayAccess $delivery
     * @param null|string $plugin_id
     * @return object
     */
    public static function extractShippingVariantAndPlugin($delivery, $plugin_id = null)
    {
        $result = ['plugin' => '', 'variant' => '', 'error' => true];

        if (is_array($delivery) or (is_object($delivery) and $delivery instanceof \ArrayAccess)) {
            $plugin = isset($delivery['shipping_plugin']) ? $delivery['shipping_plugin'] : null;
            $variant_id = isset($delivery['shipping_rate_id']) ? $delivery['shipping_rate_id'] : null;

            if ((!$plugin || !$variant_id) && isset($delivery['params']) && is_array($delivery['params'])) {
                $delivery = $delivery['params'];
                $plugin = isset($delivery['shipping_plugin']) ? $delivery['shipping_plugin'] : null;
                $variant_id = isset($delivery['shipping_rate_id']) ? $delivery['shipping_rate_id'] : null;
            }

            if ($plugin && $variant_id) {
                $result['plugin'] = $plugin;
                $result['variant'] = $variant_id;
                $result['error'] = false;
            }
            if ($plugin_id && $plugin != $plugin_id) {
                $result['error'] = true;
            }
        } else {
            $result['plugin'] = $plugin_id;
            $result['variant'] = $delivery;
            $result['error'] = false;
        }

        return (object)$result;
    }
}
