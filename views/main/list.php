<?php
/**
   @var $dataProvider yii\data\ActiveDataProvider
   @var $this yii\web\View
*/

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ListView;


    $listViewId = 'news-list';
    $gridHtmlClass = 'news-list-grid';
    $gridTableClass = 'news-list-items';

    $assets = $this->context->module->registerAsset('FrontAsset', $this);

    $this->title = Yii::t($this->context->tc, 'News');
    //$this->params['breadcrumbs'][] = $this->title;
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($this->title),
        'url' => Url::to(['index']),
    ];

    //$page = $dataProvider->pagination->page + 1;
?>
<div class="news-list">

    <h1><a href="<?= Url::to(['list']) ?>"><?= Html::encode($this->title) ?></a></h1>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'id' => $listViewId,
        'options' => ['class' => $gridHtmlClass],
        'layout' => "{pager}\n<table class=\"{$gridTableClass}\">\n{items}\n</table>\n{summary}\n{pager}",
        'itemView' => function($model, $key, $index, $widget) use($dataProvider) {
            $item = $this->context->renderPartial('list-item', [
                'model' => $model,
                'key'   => $key,
                'index' => $index,
                'widget' => $widget,
                'dataProvider' => $dataProvider,
            ]);
            return '<tr class="item">' . $item . '</tr>';
        },

    ]); ?>

</div>
