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

        return $this->render('list', compact('dataProvider'));
    }

    /**
     * View by slug action
     * @param integer $id
     */
    public function actionViewBySlug($slug)
    {
        $lh = $this->module->langHelper;
        $language = $lh::normalizeLangCode(Yii::$app->language);

        $model = null;
        $modelI18n = $this->module->model('NewsI18n')->findOne([
            'slug' => $slug,
            'lang_code' => $language,
        ]);

        // but after language switching slug for article on another language can be another
        if (empty($modelI18n)) {  // try to get language from referer
            $referer = Yii::$app->request->referrer;
            $hostInfo = Yii::$app->request->hostInfo;
            if (0 === strpos($referer, $hostInfo)) {
                $previousLanguage = substr($referer, strlen($hostInfo) + 1, 2);
                $previousLanguage = $lh::normalizeLangCode($previousLanguage);
                $modelI18n = $this->module->model('NewsI18n')->findOne([
                    'slug' => $slug,
                    'lang_code' => $previousLanguage,
                ]);
                if (!empty($modelI18n)) {  // found i18n-model for previous language
                    $model = $modelI18n->getMain()->one();  // get common model
                    if (!empty($model)) {
                        $modelsI18n = $model::prepareI18nModels($model);
                        if (!empty($modelsI18n[$language])) {
                            // do now show page with illegal slug in URL - prepare to redirect with correct slug
                            $newSlug = $modelsI18n[$language]->slug;
                            $result = Yii::$app->getUrlManager()->parseRequest(Yii::$app->request);
                            if (is_array($result)) {
                                list ($route, $routeParams) = $result;
                                $routeParams[0] = $route;
                                $routeParams['lang'] = $language;
                                $routeParams['slug'] = $newSlug;
                                $link = Yii::$app->urlManager->createUrl($routeParams);
                                return $this->redirect($link);
                            }                
                        }
                    }
                }
            }
        }
        
        if (!empty($modelI18n)) {
            $contentHelper = new $this->contentHelperClass;
            $modelI18n->body = $contentHelper::afterSelectBody($modelI18n->body);
            $model = $modelI18n->getMain()->one();
        }

        $searchModel = $this->module->model('NewsSearchFront');
        $model = $searchModel::canShow($model, $modelI18n);
        if (!$model) {
            $modelI18n = false;
        }

        return $this->render('view', compact('model', 'modelI18n'));
    }

}
