<?php

namespace asb\yii2\modules\news_2b_161124\controllers;

use asb\yii2\modules\news_1b_160430\controllers\MainController as ParentMainController;
//use asb\yii2\modules\news_1b_160430\models\NewsSearchFront;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Test example of inherited controller.
 *
 * @author ASB <ab2014box@gmail.com>
 */
class MainController extends ParentMainController
{
    /** UTC dete-time info */
    public $datetimes = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $zoneUtc = new \DateTimeZone('UTC');
        $zoneServer = new \DateTimeZone(Yii::$app->timeZone);
        $datetimeUtc = new \DateTime("now", $zoneUtc);
        $datetimeServer = new \DateTime("now", $zoneServer);
        $offsetSec = $zoneUtc->getOffset($datetimeUtc) - $zoneServer->getOffset($datetimeServer);

        $time = time();
        $this->datetimes = [
            'serverTimeUtcUnix' => $time + $offsetSec,
            'serverTimeUtc'     => date('d.m.Y H:i:s', $time + $offsetSec),
          //'serverTimeUnix'    => $time,
          //'serverTime'        => date('d.m.Y H:i:s', $time),
        ];
    }

    // example without using $this->renderData
    /**
     * @inheritdoc
     */
    public function actionList($page = 1)
    {
        //return parent::actionList($page); // original render but with inherited view + layout
        
        //$searchModel = new NewsSearchFront; // model defined in use-clause
        $searchModel = $this->module->getDataModel('NewsSearchFront'); // may be inherited model

        $params = $this->prepateListSearchParams();
        $dataProvider = $searchModel->search($params);

        $pager = $dataProvider->getPagination();
        $pager->pageSize = $this->module->params['pageSizeFront'];
        $pager->totalCount = $dataProvider->getTotalCount();

        // page number correction:
        if ($pager->totalCount <= $pager->pageSize || $page > ceil($pager->totalCount / $pager->pageSize) ) {
            $pager->page = 0; //goto 1st page if shortage records
        } else {
            $pager->page = $page - 1; //! from 0
        }

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @inheritdoc
     */
/*
    public function actionView($id)
    {
        $result = parent::actionView($id); // render result not need, only $this->renderData
        $this->renderData = ArrayHelper::merge($this->renderData, [
            'datetimes' => $this->datetimes,
        ]);
        return $this->render('view', $this->renderData);
    }
*/

    /** View by slug action */
    public function actionViewBySlug($slug)
    {
        $lh = $this->module->langHelper;
        $language = $lh::normalizeLangCode(Yii::$app->language);

        $model = null;
        $modelI18n = $this->module->model('NewsI18n')->findOne([
            'slug' => $slug,
            'lang_code' => $language,
        ]);
        if (!empty($modelI18n)) {
            $contentHelper = new $this->contentHelperClass;
            $modelI18n->body = $contentHelper::afterSelectBody($modelI18n->body);
            $model = $modelI18n->getMain()->one();
        }
        $this->renderData = [
            'model' => $model,
            'modelI18n' => $modelI18n,
        ];
        return $this->render('view', $this->renderData);
    }


}
