<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2017-2025
 */
return [
    'sender_zip'                 => [
        'title'        => 'Индекс города отправителя',
        'description'  => '',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'class'        => 'short shorter js-nrg-sender-zip',
        'pattern'      => '[0-9]{6}',
        'subject'      => 'main'
    ],
    'sender_city_code'           => [
        'title'        => 'Код города отправителя',
        'description'  => '<i class="icon10 exclamation"></i> это поле заполнится автоматически',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'readonly'     => true,
        'class'        => 'short shorter js-nrg-sender-city-code',
        'subject'      => 'main'
    ],
    'sender_city_name'           => [
        'title'        => 'Название города отправителя',
        'description'  => '<i class="icon10 info"></i> Это поле информационное, не используется плагином. Заполняется автоматически.',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'readonly'     => true,
        'class'        => 'longer js-nrg-sender-city-name',
        'subject'      => 'main'
    ],
    'pickup_price'               => [
        'title'        => 'Отправка',
        'description'  => 'Учет стоимости экспедирования груза от отправителя на склад транспортной компании',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'store',
        'options'      => [
            'store'  => 'От склада ТК',
            'sender' => 'Забрать у отправителя'
        ],
        'subject'      => 'main'
    ],
    'delivery_type'              => [
        'title'        => 'Тип доставки',
        'description'  => 'Какие варианты доставки рассчитывать',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'all',
        'options'      => [
            'all'     => 'Все',
            'todoor'  => 'До двери покупателя',
            'tostore' => 'До офиса ТК'
        ],
        'subject'      => 'main'
    ],
    'show_first'                 => [
        'title'        => 'Очередность',
        'description'  => 'Какие варианты показывать первыми',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'todoor',
        'options'      => ['todoor' => 'Сначала до двери', 'tostore' => 'Сначала до  склада'],
        'subject'      => 'main'
    ],
    'optimize'                   => [
        'title'        => 'Оптимизатор тарифа',
        'description'  => 'Возможность сократить список вариантов доставки, отобрав только самые выгодные',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'all',
        'options'      => ['all' => 'Выключить', 'cheapest' => 'Самый дешевый'],
        'subject'      => 'main'
    ],
    'city_hide'                  => [
        'title'        => 'Скрытие метода',
        'description'  => 'Если на этапе контактной информации выбран город или индекс, для которых нет доставки, то ' .
            'можно вообще не показывать метод доставки вместо надписи &laquo;Недоступно&raquo;.<br>' .
            '<i class="icon16 exclamation"></i>Но тогда у покупателя не будет шансов ввести другой почтовый индекс!',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'never',
        'options'      => ['never' => 'Не скрывать', 'sender_only' => 'Только город-отправитель', 'always' => 'Любой недоступный город'],
        'subject'      => 'main'
    ],
    'standard_parcel_dimensions' => [
        'value'        => '20x20x20',
        'title'        => 'Средние размеры отправления в сантиметрах',
        'description'  =>
            '',
        'control_type' => 'PackageSelect',
        'subject'      => 'main'
    ],
    'free_delivery_door'         => [
        'title'        => 'Бесплатная доставка до двери, от',
        'description'  => 'Сумма заказа, при которой стоимость доставки «до двери» становится бесплатной. Оставьте поле пустым, если бесплатной доставки нет. Поставьте «0» если доставка всегда бесплатная',
        'control_type' => waHtmlControl::INPUT,
        'class'        => 'short numerical',
        'value'        => '',
        'subject'      => 'main'
    ],
    'free_delivery_terminal'     => [
        'title'        => 'Бесплатная доставка до терминала, от',
        'description'  => 'Сумма заказа, при которой стоимость доставки «до терминала» становится бесплатной. Оставьте поле пустым, если бесплатной доставки нет. Поставьте «0» если доставка всегда бесплатная',
        'control_type' => waHtmlControl::INPUT,
        'class'        => 'short numerical',
        'value'        => '',
        'subject'      => 'main'
    ],
    'handling_base'              => [
        'value'        => 'order',
        'title'        => 'База расчета комплектации',
        'description'  =>
            'Базовая сумма, которая используется для расчета процентов при подсчете стоимости ' .
            'комплектации. Имеет смысл только если расчет комплектации идет в процентах. <b>Заказ</b> - стоимость ' .
            'товаров в заказе. <b>Заказ+доставка</b> - сумма стоимости заказа и <i>расчетной</i> (той, что Энергия ' .
            'насчитала) стоимости доставки',
        'control_type' => waHtmlControl::SELECT,
        'options'      => [
            'order'          => 'Заказ',
            'order_shipping' => 'Заказ+доставка',
            'shipping'       => 'Доставка',
            'formula'        => 'Формула'
        ],
        'subject'      => 'main'
    ],
    'handling_cost'              => [
        'value'        => 0,
        'title'        => 'Стоимость комплектации',
        'description'  =>
            'Дополнительная сумма, которая должна быть добавлена к результату расчета. Фиксированная сумма, ' .
            'проценты от <b>стоимости заказа</b>. Например "100" - 100 рублей, "10%" - 10 процентов. Или формула: ' .
            'в которой доступны переменные Z (стоимость заказа) и S (стоимость доставки). Подробнее о формуле ' .
            'смотрите <a href="//www.webasyst.ru/store/plugin/shipping/nrg/#formula">на странице описания плагина</a>',
        'control_type' => waHtmlControl::INPUT,
        'subject'      => 'main'
    ],
    'zero_weight_item'           => [
        'title'        => 'Товар с нулевым весом',
        'description'  => 'Если среди отправляемых товарое есть хотя бы один, вес у которого равен нулю или не указан, то расчет можно прервать и показать ошибку',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'continue',
        'options'      => ['continue' => 'Продолжить расчет', 'stop' => 'Прервать расчет'],
        'subject'      => 'main'
    ],
    'zero_weight_item_msg'       => [
        'title'        => 'Сообщение об ошибке для товара с нулевым весом',
        'description'  => 'Сообщение об ошибке, которое будет  показано, если расчет прерван из-за товара с нулевым весом',
        'control_type' => waHtmlControl::INPUT,
        'placeholder'  => 'Недоступно',
        'subject'      => 'main'
    ]
];
