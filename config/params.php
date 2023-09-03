<?php
return [
    'judgeProblemDataPath' => dirname(__FILE__) . '/../judge/data/',

    'polygonProblemDataPath' => dirname(__FILE__) . '/../polygon/data/',

    'components.formatter' => [
        'class' => app\components\Formatter::class,
        'defaultTimeZone' => 'Asia/Ho_Chi_Minh',
        'locale' => 'vi-VN',
        'dateFormat' => 'yyyy/MM/dd',
        'datetimeFormat' => 'yyyy/MM/dd HH:mm:ss',
        'thousandSeparator' => '&thinsp;',
    ],
    'components.setting' => [
        'class' => app\components\Setting::class,
    ],
];
