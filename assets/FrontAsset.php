<?php

namespace asb\yii2\modules\news_2b_161124\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class FrontAsset extends AssetBundle
{
    public $css = [
        'news2-front.css',
    ];

    public $js = [
        'news2-front.js',
    ];
    public $jsOptions = ['position' => View::POS_BEGIN];

    public $depends = [
        'asb\yii2\modules\news_1b_160430\assets\FrontAsset', // "child" asset
    ];

    public function init() {
        parent::init();
        $this->sourcePath = __DIR__ . '/front';
    }
}
