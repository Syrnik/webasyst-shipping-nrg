<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2017
 */
return array(
    'sender_zip'                 => array(
        'title'        => 'Индекс города отправителя',
        'description'  => '',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'class'        => 'short',
        'subject'      => 'main'
    ),
    'sender_city_code'           => array(
        'title'        => 'Код города отправителя',
        'description'  => '<i class="icon10 exclamation"></i> это поле заполнится автоматически',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'readonly'     => true,
        'class'        => 'short',
        'subject'      => 'main'
    ),
    'sender_city_name'           => array(
        'title'        => 'Название города отправителя',
        'description'  => '<i class="icon10 info"></i> Это поле информационное, не используется плагином. Заполняется автоматически.',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'readonly'     => true,
        'subject'      => 'main'
    ),
    'pickup_price'               => array(
        'title'        => 'Отправка',
        'description'  => 'Учет стоимости экспедирования груза от отправителя на склад транспортной компании',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'store',
        'options'      => array(
            'store'  => 'От склада ТК',
            'sender' => 'Забрать у отправителя'
        ),
        'subject'      => 'main'
    ),
    'delivery_type'              => array(
        'title'        => 'Тип доставки',
        'description'  => 'Какие варианты доставки рассчитывать',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'all',
        'options'      => array(
            'all'     => 'Все',
            'todoor'  => 'До двери покупателя',
            'tostore' => 'До офиса ТК'
        ),
        'subject'      => 'main'
    ),
    'show_first'                 => array(
        'title'        => 'Очередность',
        'description'  => 'Какие варианты показывать первыми',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'todoor',
        'options'      => array('todoor' => 'Сначала до двери', 'tostore' => 'Сначала до  склада'),
        'subject'      => 'main'
    ),
    'optimize'                   => array(
        'title'        => 'Оптимизатор тарифа',
        'description'  => 'Возможность сократить список вариантов доставки, отобрав только самые выгодные',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'all',
        'options'      => array('all' => 'Выключить', 'cheapest' => 'Самый дешевый'),
        'subject'      => 'main'
    ),
    'standard_parcel_dimensions' => array(
        'value'        => '20x20x20',
        'title'        => 'Средние размеры отправления в сантиметрах',
        'description'  =>
            '',
        'control_type' => 'PackageSelect',
        'subject'      => 'main'
    ),
    'zero_weight_item'           => array(
        'title'        => 'Товар с нулевым весом',
        'description'  => 'Если среди отправляемых товарое есть хотя бы один, вес у которого равен нулю или не указан, то расчет можно прервать и показать ошибку',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'continue',
        'options'      => array('continue' => 'Продолжить расчет', 'stop' => 'Прервать расчет')
    ),
    'zero_weight_item_msg'       => array(
        'title'        => 'Сообщение об ошибке для товара с нулевым весом',
        'description'  => 'Сообщение об ошибке, которое будет  показано, если расчет прерван из-за товара с нулевым весом',
        'control_type' => waHtmlControl::INPUT,
        'placeholder'  => 'Недоступно'
    )
);