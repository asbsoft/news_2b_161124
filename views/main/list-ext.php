<?php
/**
    @var string $html
    @var array $datetimes
*/
?>
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

<?= $html ?>

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
