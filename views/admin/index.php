<?php
/**
    @var $searchModel asb\yii2\modules\news_1b_160430\models\NewsSearch
    @var $dataProvider yii\data\ActiveDataProvider
    @var $currentId integer current item id
    @var $this yii\web\View
*/

    use asb\yii2\modules\news_1b_160430\models\Formatter;
    use asb\yii2\modules\news_2b_161124\models\NewsUpload;

    use asb\yii2\common_2_170212\assets\BootstrapCssAsset;
    use asb\yii2\common_2_170212\assets\CommonAsset;
    use asb\yii2\modules\news_1b_160430\assets\AdminAsset;

    use asb\yii2\common_2_170212\widgets\grid\ButtonedActionColumn;

    use asb\yii2\common_2_170212\widgets\Alert;

    use kartik\date\DatePicker;

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;

    $heightImage = 35; //px
    $gridViewId = 'news-grid';
    $gridHtmlClass = 'news-list-grid';

    $tc = $this->context->tcModule;

    BootstrapCssAsset::register($this); // need to move up bootstrap.css
    $assetsSys = CommonAsset::register($this);
    $assets = AdminAsset::register($this);

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

    $userIdentity = $this->context->module->userIdentity;
    $usersNamesList = method_exists($userIdentity, 'usersNames') ? $userIdentity::usersNames() : false;
    $userFilter = (Yii::$app->user->can('roleNewsModerator') && $usersNamesList) ? $usersNamesList : false;

?>
<div class="news-index">

    <h1><a href="<?= Url::to(['index']) ?>"><?= Html::encode($this->title) ?></a></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= Alert::widget(); ?>

    <table>
    <tr>
        <td>
            <?php if(Yii::$app->user->can('roleNewsAuthor')): ?>
                <?= Html::a(Yii::t($tc, 'Create News'), ['create'], ['class' => 'btn btn-success']) ?>
            <?php elseif(Yii::$app->user->can('roleNewsModerator')): ?>
                <?= Yii::t($tc, "Moderator can't create news") ?>
            <?php endif; ?>
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

    <div id="loading" class="collapse text-center"><img src="<?= $assetsSys->baseUrl ?>/img/wait.gif" /></div>

    <?= GridView::widget([
        'id' => $gridViewId,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => $gridHtmlClass],
        'formatter' => ['class' => Formatter::className(),
            'timeZone' => 'UTC'
        ],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => 'No',
                'headerOptions' => ['class' => 'align-center'],
                'contentOptions' => ['class' => 'align-right'],
            ],
            [
                'attribute' => 'image',
                'header' => false,
                'filter' => false,
                'contentOptions' => [
                    'style' => 'padding: 0px',
                    'class' => 'align-center'
                    //'class' => 'no-padding', //dont work
                ],
                'content' => function ($model, $key, $index, $column) use($heightImage, $uploadsUrl) {
                    if( !empty($model->image)) {
                        return Html::img($uploadsUrl . '/' . $model->image, [
                            'height' => $heightImage,
                        ]);
                    }
                }
            ],
            [
                'attribute' => 'title',
                'header' => Yii::t($tc, 'Title'),
                'filter' => Html::activeTextInput($searchModel, 'title', [
                    'id' => 'search-title',
                    'class' => 'form-control'
                ]),
            ],
            [
                'attribute' => 'owner_id',
                'label' => Yii::t($tc, 'Author'),
                'format' => 'username',
                'filter' => $userFilter,
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => '-' . Yii::t($tc, 'all') . '-'],
            ],
            [
                'attribute' => 'show_from_time',
                'label' => Yii::t($tc, 'Show from time (UTC)'),
                'format' => 'datetime',
                'options' => [
                    'id' => 'show_from_time',
                    'style' => 'width:240px',
                ],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute'  => 'show_from_time_begin',
                    'attribute2' => 'show_from_time_end',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => ['format' => 'yyyy-mm-dd'],
                ]),
            ],
            [
                'attribute' => 'show_to_time',
                'label' => Yii::t($tc, 'Show to time (UTC)'),
                'format' => 'datetime',
                'options' => [
                    'id' => 'show_to_time',
                    'style' => 'width:240px',
                ],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute'  => 'show_to_time_begin',
                    'attribute2' => 'show_to_time_end',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => ['format' => 'yyyy-mm-dd'],
                ]),
            ],
            [
                'attribute' => 'is_visible',
                'label' => Yii::t($tc, 'Show'),
                'format' => 'boolean',
                'filter' => [
                    true  => Yii::t('yii', 'Yes'),
                    false => Yii::t('yii', 'No'),
                ],
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => '-' . Yii::t($tc, 'all') . '-'],
                'options' => [
                    'style' => 'width:85px', //'class' => 'width-min',
                ],
            ],
            [
                //'label' => Yii::t($tc, 'ID'),
                'attribute' => 'id',
                'format' => 'text',
                'headerOptions' => ['class' => 'align-center'],
                'contentOptions' => ['class' => 'align-right'],
                'options' => ['style' => 'width:50px'],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'style' => 'padding:5px',
                    //'maxlength' => 6,
                ],
            ],
            [
              //'class' => 'yii\grid\ActionColumn',
                'class' => ButtonedActionColumn::className(),
                'header' => Yii::t($tc, 'Actions'),
                'buttonSearch' => Html::submitInput(Yii::t($tc, 'Find'), [
                    'id' => 'search-button', 'class' => 'btn',
                ]),
                'buttonClear' => Html::buttonInput('C', [
                    'id' => 'search-clean',
                    'class' => 'btn btn-danger',
                    'title' => Yii::t($tc, 'Clean search fields'),
                ]),
              //'buttonOptions' => $this->params['buttonOptions'],
                'headerOptions' => ['class' => 'align-center'],
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                //'template' => '{change-visible} {update} {delete}',
                'template' => '{change-visible} {view} {update} {export} &nbsp;&nbsp;&nbsp; {delete}', //++ v2
                'buttons' => [
                    'change-visible' => function($url, $model, $key) use($pager, $formName, $tc) {
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
                    },
                    'delete' => function($url, $model, $key) use($pager, $formName, $tc) {
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
                    },
                    'export' => function($url, $model, $key) use($pager, $formName, $tc) { //++ v2
                        $options = [
                            'title' => Yii::t($tc, 'Archivate'),
                            'aria-label' => Yii::t($tc, 'Archivate'),
                            //'data-pjax' => '0',
                            //'data-confirm' => Yii::t($tc, 'Archivate item ID={id}?', ['id' => $key]),
                            'class' => 'buttons-export',
                            'id' => 'btn-export-' . $key,
                        ];
                        $url = Url::to(['export'
                          , 'id' => $model->id
                          , 'sort' => $this->params['buttonOptions']['data']['sort']
                          , $formName => $this->params['buttonOptions']['data']['search']
                          , 'page' => $pager->page + 1
                        ]);
                        return Html::a("<span class='glyphicon glyphicon-download-alt'></span>", $url, $options);
                    },
                ],
            ],
        ],
    ]); ?>

</div>

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

        jQuery('.{$gridHtmlClass} table tr').each(function(index) {
            var elem = jQuery(this);
            var id = elem.attr('data-key');
            if (id == '{$currentId}') {
               elem.addClass('bg-success'); //?? overwrite by .table-striped > tbody > tr:nth-of-type(2n+1)
               elem.css({'background-color': '#DFD'}); // work always
            }
        });

        jQuery('.buttons-export').bind('click', function() {
            jQuery('.alert').hide();
            //jQuery('#loading').show();

            var elem = jQuery(this);
            //elem.attr('disabled', 'disabled'); //?? not work
            elem.addClass('disabled');
            elem.hide();
            elem.after('<img class=\"tmp\" src=\"{$assetsSys->baseUrl}/img/wait-smaller.gif\" />');

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
