<?php
//!! only add changes - they will merged with parent
return  [
/*
    'dependencies' => [
        'asb\yii2\modules\news_1b_160430\Module', // not need: will set in BaseUniModule
    ],
*/

    // shared models
    'models' => [ // alias => class name or object array
        'News'      => 'asb\yii2\modules\news_2b_161124\models\News',
        'NewsI18n'  => 'asb\yii2\modules\news_2b_161124\models\NewsI18n',
        'NewsQuery' => 'asb\yii2\modules\news_2b_161124\models\NewsQuery',
//      'NewsSearchFront' => 'asb\yii2\modules\news_1b_160430\models\NewsSearchFront',
    ],

    // used assets
    'assets' => [ // alias => class name
        //'AdminAsset' => 'asb\yii2\modules\news_2b_161124\assets\AdminAsset',
        'FrontAsset' => 'asb\yii2\modules\news_2b_161124\assets\FrontAsset',
    ],
];
