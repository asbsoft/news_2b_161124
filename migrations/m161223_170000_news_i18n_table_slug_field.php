<?php

use asb\yii2\modules\news_2b_161124\Module;
use asb\yii2\modules\news_2b_161124\models\NewsI18n;

use yii\db\Migration;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class m161223_170000_news_i18n_table_slug_field extends Migration
{
    protected $tableName;

    public function init()
    {
        parent::init();

        // if problems with autoload (classes not found):
        //Yii::setAlias('@asb/yii2', '@vendor/asbsoft/yii2-common_2_170212');
        //Yii::setAlias('@asb/yii2/modules', '@vendor/asbsoft/yii2module');

        $this->tableName = NewsI18n::tableName();
    }
    
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'slug', $this->string(255));
    }

    public function safeDown()
    {
        //echo basename(__FILE__, '.php') . " cannot be reverted.\n";
        //return false;
        $this->dropColumn($this->tableName, 'slug');
    }

}
