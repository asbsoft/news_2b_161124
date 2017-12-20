<?php
/**
 * Test example of inheritance
 * @author ASB <ab2014box@gmail.com>
 */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */

$datetimes = $this->context->datetimes;

?>
<?php $this->startParent() ?>
    <?php $this->startBlock('title') ?>

        <?php $this->parentBlock() ?>

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

    <?php $this->stopBlock('title') ?>
<?php $this->stopParent() ?>
