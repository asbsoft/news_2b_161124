<?php

use asb\yii2\modules\news_1b_160430\models\News;

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Test example of inherited controller.
 *
 * @var asb\yii2\modules\news_1b_160430\models\News|empty $model
 * @var asb\yii2\modules\news_1b_160430\models\NewsI18n|empty $modelI18n
 * 
 * @var array $datetimes
 *
 * @author ASB <ab2014box@gmail.com>
 */

    $heightImage = 100;

    // Instead of
    // use asb\yii2\modules\news_1b_160430\assets\FrontAsset;
    // $assets = FrontAsset::register($this);
    // use inheritance:
    $assets = $this->context->module->registerAsset('FrontAsset', $this);

    if (empty($modelI18n)) $model = false;

    $uploadsUrl = Yii::getAlias($this->context->module->params['uploadsNewsDir']);

?>
<div class="news-view">

    <?php if (empty($model)): ?>
        <h1><?= Yii::t($this->context->tc, 'Such news not found') ?></h1>
    <?php else: ?>

       <div class="js-time" data-unixtime="<?= $model->unix_show_from_time ?>"><?= $model->show_from_time ?></div>

        <h1><?= Html::encode($modelI18n->title) ?></h1>

       <div class="add-news-data">
           <h5><?= Yii::t($this->context->tc, "It's modules inheritance possibilities demo only") ?></h5>
           <h5>
               <?= Yii::t($this->context->tc, 'server time (UTC)') ?>
               <span class="local-time"><?= $datetimes['serverTimeUtc'] ?></span>
               &nbsp;&nbsp;&nbsp;
               <?= Yii::t($this->context->tc, 'client time') ?>
               <span class="js-time" data-unixtime="<?= $datetimes['serverTimeUtcUnix'] ?>"><?= $datetimes['serverTimeUtc'] ?></span>
           </h5>
       </div>

        <?php
            if(!empty($model->image)) {
                echo Html::img($uploadsUrl . '/' . $model->image, [
                    'height' => $heightImage,
                    'class' => 'news-header-image',
                ]);
            }
        ?>

        <?= $modelI18n->body ?>

    <?php endif; ?>
</div>

<?php
    // show news date-time according to client time zone from news UTC-time
    $this->registerJs("
        jQuery('.js-time').each(function(index) {
            var elem = jQuery(this);
            var data = elem.data();
            elem.html(utcToLocalDatetime(data.unixtime));
        });
    ");
?>
