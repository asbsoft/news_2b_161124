<?php

namespace asb\yii2\modules\news_2b_161124\models;

use asb\yii2\modules\news_1b_160430\models\NewsI18n as BaseNewsI18n;

use asb\yii2\common_2_170212\behaviors\SluggableBehavior;

use Yii;
use yii\helpers\ArrayHelper;
use yii\validators\UniqueValidator;

/**
 * Model class for table "{{%news_i18n}}".
 *
 * @property integer $id
 * @property integer $news_id
 * @property string $title
 * @property string $body
 *
 * @property string $slug addition field
 */
class NewsI18n extends BaseNewsI18n
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'slugAttribute' => 'slug',
                'immutable' => true, // can't auto change after assign - may be it is own version of slug

                'ensureUnique' => true,
                'uniqueValidator' => [
                    //'class' => UniqueValidator::className(),
                    'targetAttribute' => ['lang_code', 'slug'],
                ],

                'allowEmptySlug' => true, //+
            ],
        ], parent::behaviors());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = ArrayHelper::merge(parent::rules(), [
            ['slug', 'string', 'max' => 255],
            ['slug', 'match',
                //'pattern' => '/^[a-z0-9_\-\.]+$/i',
                //'message' => Yii::t($this->tcModule, 'Only latin letters, digits, hyphen, underline and point'),
                'pattern' => '/^[a-z0-9\-]+$/i', 'message' => Yii::t($this->tcModule, 'only latin letters, digits and hyphen'),
            ],
            ['slug', 'unique',
                'targetAttribute' => ['lang_code', 'slug'],
                'message' => Yii::t($this->tcModule
                  , 'such slug already used for this language {lang_code}', ['lang_code' => $this->lang_code])
            ],
            ['slug', 'match',
                'not' => true,
                'pattern' => '/^[0-9]+$/i',
                'message' => Yii::t($this->tcModule, "slug can't contain only digits"),
            ],
        ]);
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = ArrayHelper::merge(parent::attributeLabels(), [
            'slug' => Yii::t($this->tcModule, 'Slug'),
        ]);
        return $labels;
    }
}

