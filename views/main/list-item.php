<?php
/**
    @var $model
    @var $key
    @var $index
    @var $widget
    @var $dataProvider
*/

    use yii\helpers\Html;


    $assets = $this->context->module->registerAsset('FrontAsset', $this);

    //$page = $dataProvider->pagination->page + 1;var_dump($page);
    $url = ['view', 'id' => $model->id
               //, 'page' => $page
           ];

    $langHelper = $this->context->module->langHelper;
    $lang = $langHelper::normalizeLangCode(Yii::$app->language, true);
    $slug = $model->getSlug($lang);
    if (!empty($slug)) {
        $url = ['view-by-slug', 'slug' => $slug
                   //, 'page' => $page
               ];
    }

    $uploadsUrl = Yii::getAlias($this->context->module->params['uploadsNewsDir']);

?>
   <td class="item-image">
       <?php if(!empty($model->image)): ?>
           <?= Html::img($uploadsUrl . '/' . $model->image, [
                   'class' => 'thumbnail',
               ]); ?>
       <?php else: ?>
           <img class="thumbnail" src="<?= $assets->baseUrl ?>/img/no-picture.jpg" />
       <?php endif; ?>
   </td>

   <td>&nbsp;</td>

   <td class="item-time">
       <div class="js-time" data-unixtime="<?= $model->unix_show_from_time ?>"><?= $model->show_from_time ?></div>
       <?= '' //$model->show_from_time ?>
   </td>

   <td>&nbsp;</td>

   <td class="item-title">
        <?= Html::a(Html::encode($model->title), $url, ['title' => $model->title]); ?>
   </td>
