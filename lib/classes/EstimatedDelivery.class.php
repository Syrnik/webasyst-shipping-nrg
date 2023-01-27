<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace Syrnik\nrgShipping;

use DateInterval;
use DateTimeImmutable;
use Exception;
use waDateTime;
use waException;

/**
 * Class EstimatedDelivery
 * @package Syrnik\nrgShipping
 */
class EstimatedDelivery
{
    protected $MinDays = 0;

    protected $MaxDays = 0;

    /** @var DateTimeImmutable */
    protected $Departure;

    /**
     * EstimatedDelivery constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $key => $param) {
            if (substr($key, 0, 1) === '_') {
                continue;
            }

            $_method = "set$key";
            if (method_exists($this, $_method)) {
                $this->$_method($param);
            }
        }

        if (!$this->Departure) {
            // конструктор immutable бросает исключения и штром ругается, что я их не ловлю. Но тут и ловить нечего
            $this->Departure = date_create_immutable();
        }
    }

    /**
     * @param string $range
     * @param string $delimiter
     * @return $this
     */
    public function parseRange($range, $delimiter = '-')
    {
        $range = explode($delimiter, $range, 2);
        $_range[] = (int)trim(ifset($range, '0', 0));
        $_range[] = (int)trim(ifset($range, '1', 0));

        $this->setMinDays(min($_range));
        $this->setMaxDays(max($_range));

        if ($this->getMinDays() == 0) {
            $this->setMinDays($this->getMaxDays());
        }

        return $this;
    }

    /**
     * Парсит строки '1 день', '1-2 дня', '3-5 дней', которые отдаёт ТК Энергия
     *
     * @param $range
     * @return $this
     * @throws waException
     */
    public function parseRegexRange($range)
    {
        $matches = [];
        $_range = [0, 0];

        if (preg_match('/^(\d+)-?(\d+)?\s(день|дня|дней)/iu', $range, $matches)) {
            $_range[0] = (int)$matches[1];
            $_range[1] = (int)$matches[2];
        } else {
            throw new waException('Ошибка разбора сроков доставки из строки: ' . $range);
        }

        $this->setMinDays(min($_range));
        $this->setMaxDays(max($_range));

        if ($this->getMinDays() == 0) {
            $this->setMinDays($this->getMaxDays());
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getMinDays()
    {
        return $this->MinDays;
    }

    /**
     * @param int $MinDays
     * @return EstimatedDelivery
     */
    public function setMinDays($MinDays)
    {
        $this->MinDays = $MinDays;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDays()
    {
        return $this->MaxDays;
    }

    /**
     * @param int $MaxDays
     * @return EstimatedDelivery
     */
    public function setMaxDays($MaxDays)
    {
        $this->MaxDays = $MaxDays;
        return $this;
    }

    /**
     * Это один и тот же срок? true в случае если min === max
     *
     * @return bool
     */
    public function isExactDay()
    {
        return $this->getMinDays() === $this->getMaxDays();
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDeparture()
    {
        return $this->Departure;
    }

    /**
     * @param DateTimeImmutable $Departure
     * @return EstimatedDelivery
     */
    public function setDeparture(DateTimeImmutable $Departure)
    {
        $this->Departure = $Departure;
        return $this;
    }

    /**
     * @param string $departure
     * @param string $format
     * @return $this
     */
    public function setDepartureString($departure, $format = 'Y-m-d H:i:s')
    {
        if ($format) {
            $_departure = date_create_immutable_from_format($format, $departure);
        } else {
            $_departure = date_create_immutable($departure);
        }

        return $this->setDeparture($_departure instanceof DateTimeImmutable ? $_departure : date_create_immutable());
    }

    /**
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getMinDateTime()
    {
        return $this->getDeparture()->add(new DateInterval('P' . $this->getMinDays() . 'D'));
    }

    /**
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getMaxDateTime()
    {
        return $this->getDeparture()->add(new DateInterval('P' . $this->getMaxDays() . 'D'));
    }

    /**
     * @param string $format
     * @return string
     * @throws waException
     * @throws Exception
     */
    public function getWebasystEstDelivery($format = 'humandate')
    {
        if ($this->isExactDay()) {
            return waDateTime::format($format, $this->getMinDateTime()->getTimestamp());
        }

        return waDateTime::format($format, $this->getMinDateTime()->getTimestamp()) . ' — ' . waDateTime::format($format, $this->getMaxDateTime()->getTimestamp());
    }

    /**
     * @return array|string
     * @throws Exception
     */
    public function getWebasystDeliveryDates()
    {
        if ($this->isExactDay()) {
            return $this->getMinDateTime()->format('Y-m-d H:i:s');
        }

        return [$this->getMinDateTime()->format('Y-m-d H:i:s'), $this->getMaxDateTime()->format('Y-m-d H:i:s')];
    }

    /**
     * @param string $format
     * @return array
     * @throws waException|Exception
     */
    public function getWebasystShippingParams($format = 'humandate')
    {
        return array(
            'est_delivery'  => $this->getWebasystEstDelivery($format),
            'delivery_date' => $this->getWebasystDeliveryDates()
        );
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->getMaxDays() >= $this->getMinDays()) && ($this->getMinDays() >= 0);
    }
}