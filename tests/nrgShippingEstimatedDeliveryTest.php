<?php

use PHPUnit\Framework\TestCase;

/**
 * Даты доставки считаются от departure_datetime (готовность заказа к отгрузке),
 * а не от момента расчёта. Проверка на сценариях из FIVESHIP-495 (NRG-601):
 * в nrg дефекты WaShippingUtils::calcDaysToShip() не проявляются, т.к. он не используется.
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */
class nrgShippingEstimatedDeliveryTest extends TestCase
{
    /**
     * Отправление меньше чем через сутки от «сейчас» (вечер воскресенья → утро понедельника):
     * дата не должна съезжать на день раньше из-за усечения дробной части дней.
     */
    public function testDatesAreCountedFromDepartureNotFromNow()
    {
        $departure = (new DateTimeImmutable('tomorrow'))->setTime(9, 0);

        $delivery = (new nrgShippingEstimatedDelivery())
            ->setDepartureString($departure->format('Y-m-d H:i:s'))
            ->parseRegexRange('1-2 дней');

        self::assertSame(
            [
                $departure->modify('+1 day')->format('Y-m-d H:i:s'),
                $departure->modify('+2 days')->format('Y-m-d H:i:s'),
            ],
            $delivery->getWebasystDeliveryDates()
        );
    }

    /**
     * Результат для одного и того же departure_datetime не зависит от текущего времени сервера.
     */
    public function testDatesDoNotDependOnCurrentTime()
    {
        $delivery = (new nrgShippingEstimatedDelivery())
            ->setDepartureString('2026-09-27 21:30:00')
            ->parseRegexRange('3-5 дней');

        self::assertSame(['2026-09-30 21:30:00', '2026-10-02 21:30:00'], $delivery->getWebasystDeliveryDates());
    }

    public function testSingleDayInterval()
    {
        $delivery = (new nrgShippingEstimatedDelivery())
            ->setDepartureString('2026-09-27 21:30:00')
            ->parseRegexRange('1 день');

        self::assertTrue($delivery->isExactDay());
        self::assertSame('2026-09-28 21:30:00', $delivery->getWebasystDeliveryDates());
    }

    /**
     * Объект переиспользуется в calculate() для всех вариантов доставки — интервал
     * предыдущего варианта не должен влиять на следующий.
     */
    public function testReuseForSeveralVariants()
    {
        $delivery = (new nrgShippingEstimatedDelivery())->setDepartureString('2026-09-27 21:30:00');

        $delivery->parseRegexRange('3-5 дней');
        $delivery->parseRegexRange('1 день');

        self::assertSame('2026-09-28 21:30:00', $delivery->getWebasystDeliveryDates());
    }
}
