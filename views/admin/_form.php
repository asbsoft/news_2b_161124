<?php
/**
 * Test example of inheritance
 * @author ASB <ab2014box@gmail.com>
 */
    /* @var $model asb\yii2\modules\news_1b_160430\models\News */
    /* @var $modelsI18n array of asb\yii2\modules\news_1b_160430\models\NewsI18n */
    /* @var $this yii\web\View */
    /* @var $form yii\widgets\ActiveForm */
    /* @var $activeTab string */

    use asb\yii2\common_2_170212\widgets\ckeditor\CkEditorWidget;

    use yii\widgets\ActiveForm;

    // defaults
    if (empty($heightEditor)) $heightEditor = 250; //px

    $langHelper = $this->context->module->langHelper;
    $editAllLanguages = empty($this->context->module->params['editAllLanguages'])
                      ? false : $this->context->module->params['editAllLanguages'];
    $languages = $langHelper::activeLanguages($editAllLanguages);

    $editorOptions = [
        'height' => $heightEditor,
        'language' => substr(Yii::$app->language, 0, 2),
      
        'preset' => 'full',     // full editor
      //'preset' => 'standard', // middle
      //'preset' => 'basic',    // minimal editor

      //'inline' => false, // default
        'filter' => 'image',
    ];

    if (empty($model->id)) { // news not create yet - can't load images
        $managerOptions = false;
    } else {
        $elfController = [$this->context->module->uniqueId . '/el-finder', 'id' => $model->id];
        $managerOptions = [
            'controller' => $elfController,
            'rootPath' => $this->context->module->params['uploadsNewsDir'] . '/' . $model::getImageSubdir($model->id),
            'filter' => 'image',
        ];
    }

    $form = ActiveForm::begin([
              'options' => ['enctype' => 'multipart/form-data'],
              'enableClientValidation' => false, // disable JS-validation 
              'enableClientScript' => false, // form will not generate any JavaScript
    ]);

    $addParentData = compact('heightEditor');

?>
<?php $this->startParent($addParentData) ?>

    <?php $this->startBlock('tab-content') ?>
        <div class="tab-content">
            <?php // multi-lang part - content
              foreach ($languages as $langCode => $lang):
                  $countryCode2 = strtolower(substr($langCode, 3, 2));
                  $flag = '<span class="flag f16"><span class="flag ' . $countryCode2 . '" title="' . $lang->name_orig . '"></span></span>';
                  $labels = $modelsI18n[$langCode]->attributeLabels();
                  //var_dump($modelsI18n[$langCode]->attributes);
            ?>
            <div id="tab-<?= $langCode ?>"
                class="tab-pane <?php if ($activeTab == $langCode): ?>active<?php endif; ?>"
            >
                <?= $form->field($modelsI18n[$langCode], "[{$langCode}]title",[
                        'options' => [
                            'class'=>'news-title',
                        ],
                    ])->label($flag . ' ' . $labels['title'])
                      ->textInput() ?>
<!-- slug -->
                <?= $form->field($modelsI18n[$langCode], "[{$langCode}]slug",[
                        'options' => [
                            'class'=>'news-title',
                        ],
                    ])->label('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $labels['slug'])
                      ->textInput() ?>
<!-- /slug -->
                <?= $form->field($modelsI18n[$langCode], "[{$langCode}]body")
                    ->label(false)
                  //->label($flag . ' ' . $labels['body'])
                  //->textarea(['rows' => $rowsCountTextarea]) // for debug
                    ->widget(CkEditorWidget::className(), [
                        'id' => "editor-{$langCode}",
                        //'inputOptions' => ['value' => $modelsI18n[$langCode]->body],
                        'editorOptions' => $editorOptions,
                        'managerOptions' => $managerOptions,
                    ])
                ?>

                <?php //echo 'init:' . mb_strlen($modelsI18n[$langCode]->body, 'UTF-8') . '-utf8/bytes:' . strlen($modelsI18n[$langCode]->body); ?>
            
            </div>
            <?php endforeach; ?>
        </div>
    <?php $this->stopBlock('tab-content') ?>

<?php $this->stopParent() ?>
