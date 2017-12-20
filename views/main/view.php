<?php
/**
 * Test example of inheritance
 * @author ASB <ab2014box@gmail.com>
 */
    /* @var $this asb\yii2\common_2_170212\web\UniView */
    /* @var $model asb\yii2\modules\news_1b_160430\models\News|empty */
    /* @var $modelI18n asb\yii2\modules\news_1b_160430\models\NewsI18n|empty */

    $datetimes = $this->context->datetimes;

?>
<?php $this->startParent() ?>
    <?php $this->startBlock('article/subtitle') ?>
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
    <?php $this->stopBlock('article/subtitle') ?>
<?php $this->stopParent() ?>
