<?php
use yii\helpers\ArrayHelper;
$lang = 'ru';
$result = [
    'News' => 'Новости+', // overwrite inherited

    "It's modules inheritance possibilities demo only"
 => "Это только демонстрация возможностей наследования модулей",
    'server time (UTC)' => 'время на сервере (UTC)',
    'client time' => 'время клиента',
];

// todo: need new i18n translations class instead of PhpMessageSource to avoid this:
$parentFile = dirname(dirname(dirname(__DIR__))) . "/news_1b_160430/messages/{$lang}/" . basename(__FILE__);
//$result = ArrayHelper::merge(include($parentFile), $result);

return $result;
