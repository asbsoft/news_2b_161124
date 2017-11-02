<?php

namespace asb\yii2\modules\news_2b_161124\models;

use asb\yii2\modules\news_1b_160430\models\NewsQuery as BaseNewsQuery;

use Yii;

class NewsQuery extends BaseNewsQuery
{
    /**
     * @inheritdoc
     * @return News[]|array
     */
    public function all($db = null)
    {
        $this
            ->select([
                'main.*',
                'UNIX_TIMESTAMP(main.show_from_time) AS unix_show_from_time',
                'i18n.title AS title',
                'i18n.body AS body',
                'i18n.slug AS slug', //+
            ]);
        //return parent::all($db); //error
        //$rows = $this->createCommand($db)->queryAll(); // from ActiveQuery
        //return $this->populate($rows);
        return parent::all($db);
    }
}
