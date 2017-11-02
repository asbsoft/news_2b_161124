<?php

namespace asb\yii2\modules\news_2b_161124\models;

use yii\base\Model;

class NewsUpload extends Model
{
    public $archfile;
    public $module;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['archfile', 'file',
                'extensions' => 'zip',
                'checkExtensionByMimeType' => true,
                'maxSize' => $this->module->params['maxImportArchSize'],
            ],
        ];
    }
}

