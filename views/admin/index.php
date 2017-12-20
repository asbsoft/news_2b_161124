<?php
/**
 * Test example of inheritance
 * @author ASB <ab2014box@gmail.com>
 */
    /* @var $searchModel asb\yii2\modules\news_1b_160430\models\NewsSearch */
    /* @var $dataProvider yii\data\ActiveDataProvider */
    /* @var $currentId integer current item id */
    /* @var $this yii\web\View */

    use asb\yii2\modules\news_2b_161124\models\NewsUpload;
    use asb\yii2\modules\news_1b_160430\models\Formatter;

    //use asb\yii2\modules\news_1b_160430\assets\AdminAsset;
    use asb\yii2\common_2_170212\assets\BootstrapCssAsset;
    use asb\yii2\common_2_170212\assets\CommonAsset;

    use asb\yii2\common_2_170212\widgets\grid\ButtonedActionColumn;

    use asb\yii2\common_2_170212\widgets\Alert;

    use kartik\date\DatePicker;

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\helpers\ArrayHelper;
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;

    $heightImage = 35; //px
    $gridViewId = 'news-grid';
    $gridHtmlClass = 'news-list-grid';

    $tc = $this->context->tcModule;

    BootstrapCssAsset::register($this); // need to move up bootstrap.css
    $assetsSys = CommonAsset::register($this);
    $assets = $this->context->module->registerAsset('AdminAsset', $this);//$assets = AdminAsset::register($this);

    $this->title = Yii::t($tc, 'News');
    if (!empty(Yii::$app->params['adminPath'])) {
        $this->params['breadcrumbs'][] = [
            'label' => Yii::t($tc, 'Admin startpage'),
            'url' => ['/' . Yii::$app->params['adminPath']],
        ];
    }
    //$this->params['breadcrumbs'][] = $this->title;
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($this->title),
        'url' => Url::to(['index']),
    ];

    $formName = basename($searchModel::className());
    $paramSearch = Yii::$app->request->get($formName, []);
    foreach ($paramSearch as $key => $val) {
        if (empty($val)) unset($paramSearch[$key]);
    }

    $paramSort = Yii::$app->request->get('sort', '');
    $pager = $dataProvider->getPagination();
    $this->params['buttonOptions'] = ['data' => ['search' => $paramSearch, 'sort' => $paramSort, 'page' => $pager->page + 1]];

    $loadNewsModel = new NewsUpload;
    $loadNewsModel->module = $this->context->module;
    $uploadsUrl = Yii::getAlias($this->context->module->params['uploadsNewsDir']);

    // GridView data redefine:
    $buttonChangeVisible = function($url, $model, $key) use($pager, $formName, $tc)
    {
        //$icon = $model->is_visible ? 'minus' : 'plus';
        $icon = $model->is_visible ? 'ok' : 'minus';
        $title = $model->is_visible ? Yii::t($tc, 'Hide')
                                    : Yii::t($tc, 'Show');
        //$confirm = Yii::t($tc, 'Are you sure to change visibility of this article?'),
        $confirm = $model->is_visible
            ? Yii::t($tc, 'Are you sure to hide news ID={id}?', ['id' => $key])
            : Yii::t($tc, 'Are you sure to set visible to news ID={id}?', ['id' => $key]);
        $options = array_merge([
            'title' => $title,
            'aria-label' => $title,
            'data-pjax' => '0',
            'data-confirm' => $confirm,
        ], $this->params['buttonOptions']);
        $url = Url::to(['change-visible'
          , 'id' => $model->id
          , 'sort' => $this->params['buttonOptions']['data']['sort']
          , $formName => $this->params['buttonOptions']['data']['search']
          , 'page' => $pager->page + 1
        ]);
        return Html::a("<span class='glyphicon glyphicon-{$icon}'></span>", $url, $options);
    };
    $buttonDelete = function($url, $model, $key) use($pager, $formName, $tc)
    {
        $options = [
            'title' => Yii::t('yii', 'Delete'),
            'aria-label' => Yii::t('yii', 'Delete'),
            'data-confirm' => Yii::t($tc
                                , 'Are you sure you want to delete this item with ID={id}?', ['id' => $key]),
            'data-method' => 'post',
            'data-pjax' => '0',
        ];
        //$options = array_merge($options, $this->params['buttonOptions']);

        // add to url sort criteria and page number - to return after deletion to same page
        $params = is_array($key) ? $key : ['id' => (string) $key];
        $params['page'] = $this->params['buttonOptions']['data']['page'];
        $params['sort'] = $this->params['buttonOptions']['data']['sort'];
        $params[$formName] = $this->params['buttonOptions']['data']['search'];
        $params[0] = 'delete';
        $url = Url::toRoute($params);

        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
    };
    $buttonExport = function($url, $model, $key) use($pager, $formName, $tc, $assetsSys)
    {
        $options = [
            'title' => Yii::t($tc, 'Archivate'),
            'aria-label' => Yii::t($tc, 'Archivate'),
            //'data-pjax' => '0',
            //'data-confirm' => Yii::t($tc, 'Archivate item ID={id}?', ['id' => $key]),
            'class' => 'buttons-export',
            'id' => 'btn-export-' . $key,
            'data-id' => $key,
        ];
        $url = Url::to(['export'
          , 'id' => $model->id
          , 'sort' => $this->params['buttonOptions']['data']['sort']
          , $formName => $this->params['buttonOptions']['data']['search']
          , 'page' => $pager->page + 1
        ]);
        return Html::a("<span class='glyphicon glyphicon-download-alt'></span>", $url, $options)
            . Html::img("{$assetsSys->baseUrl}/img/wait-smaller.gif", [
                 'id' => 'waiter-' . $key,
                 'class' => 'tmp',
                 'style' => 'display: none',
              ]);
    };
    $listItemButtons = [
        'change-visible' => $buttonChangeVisible,
        'delete' => $buttonDelete,
        'export' => $buttonExport,
    ];
    $buttonsTemplate = '{change-visible} {view} {update} {export} &nbsp;&nbsp;&nbsp; {delete}';

    $addParentData = compact('buttonsTemplate', 'listItemButtons');

?>
<?php $this->startParent($addParentData) ?>
    <?php $this->startBlock('buttons') ?>
        <table>
        <tr>
            <td>
                <?php $this->parentBlock() ?>
            </td>
            <td>&nbsp;</td>
            <td>
                <?php $form = ActiveForm::begin([
                    'action' => ['import', 'page' => $pager->page + 1],
                    'id' => 'load-form',
                    'options' => ['enctype' => 'multipart/form-data'],
                    'enableClientValidation' => false, // disable JS-validation 
                    'enableClientScript' => false, // form will not generate any JavaScript
                ]) ?>
                    <?= $form->field($loadNewsModel, 'archfile', [
                            'labelOptions' => ['label' => false],
                            'options' => ['class' => 'collapse'],
                        ])->fileInput([
                            'id' => 'file-select-field',
                            'class' => 'form-control',
                        ]) ?>
                    <?= Html::submitButton(Yii::t($tc, 'Load news from archieve'), [
                            'id' => 'load-button',
                            'class' => 'btn btn-success',
                        ]) ?>
                <?php ActiveForm::end(); ?>
            </td>
        </tr>
        </table>
    <?php $this->stopBlock('buttons') ?>

<?php $this->stopParent() ?>

<?php
    $this->registerJs("
        jQuery('#load-button').bind('click', function() {
            jQuery('#file-select-field').click();
            return false;
        });
        jQuery('#file-select-field').change(function() {
            jQuery('#load-button').attr('disabled', 'disabled');
            jQuery('#load-button').after('<img class=\"tmp\" src=\"{$assetsSys->baseUrl}/img/wait-middle.gif\" />');
            jQuery('#load-form').submit();
        });
        jQuery('.buttons-export').bind('click', function() {
            jQuery('.alert').hide();
            //jQuery('#loading').show();

            var elem = jQuery(this);
            elem.addClass('disabled');
            elem.hide();
            var id = elem.data('id');
            jQuery('#waiter-'+id).show();

            setTimeout(function(){
                //jQuery('#loading').hide();
                jQuery('.tmp').remove();
                elem.show();
                //jQuery('.buttons-export').attr('disabled', '');
                //jQuery('.buttons-export').attr('disabled', false); //??
                jQuery('.buttons-export').removeClass('disabled');
            }, 3000);
            return true;
        });
    ");
?>
