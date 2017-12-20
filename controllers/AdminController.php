<?php

namespace asb\yii2\modules\news_2b_161124\controllers;

use asb\yii2\modules\news_1b_160430\controllers\AdminController as BaseAdminController;

use asb\yii2\modules\news_2b_161124\models\NewsArchieve;
use asb\yii2\modules\news_2b_161124\models\NewsUpload;

use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Test example of inherited controller.
 *
 * @author ASB <ab2014box@gmail.com>
 */
class AdminController extends BaseAdminController
{
    public $tmpDir = '@runtime/news-tmp'; // default

    /** @var integer Time (in hours) to save exported news in temporary area. If 0 clear all */
    protected $_gcExportOutOfDateHours = 48;

    /** @var integer the probability (in percents) that garbage collection should be performed */
    protected $_gcProbability = 10;

    /** UTC dete-time info */
    public $datetimes = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (isset($this->module->params['gcExportOutOfDateHours'])) {
            $this->_gcExportOutOfDateHours = $this->module->params['gcExportOutOfDateHours'];
        }
        if (isset($this->module->params['gcProbability'])) {
            $this->_gcProbability = $this->module->params['gcProbability'];
        }
        if (isset($this->module->params['tmpDir'])) {
            $this->tmpDir = $this->module->params['tmpDir'];
        }
        $this->tmpDir = Yii::getAlias($this->tmpDir);
        FileHelper::createDirectory($this->tmpDir);

        // Same as in MainController
        $zoneUtc = new \DateTimeZone('UTC');
        $zoneServer = new \DateTimeZone(Yii::$app->timeZone);
        $datetimeUtc = new \DateTime("now", $zoneUtc);
        $datetimeServer = new \DateTime("now", $zoneServer);
        $offsetSec = $zoneUtc->getOffset($datetimeUtc) - $zoneServer->getOffset($datetimeServer);
        $time = time();
        $this->datetimes = [ // need for render views/main/view.php in actionView
            'serverTimeUtcUnix' => $time + $offsetSec,
            'serverTimeUtc'     => date('d.m.Y H:i:s', $time + $offsetSec),
          //'serverTimeUnix'    => $time,
          //'serverTime'        => date('d.m.Y H:i:s', $time),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'][] = [
            'actions' => ['export', 'import'],
            'allow' => true,
            'roles' => ['roleAdmin', 'roleNewsAuthor', 'roleNewsModerator'],
        ];
        $behaviors['verbs']['actions']['import'] = ['post'];
        return $behaviors;
    }

    /**
     * Garbage collection in $this->tmpDir. Skip only created in latest $this->_gcExportOutOfDateHours hours.
     */
    public function gc($force = false)
    {
        if ($force || mt_rand(0, 100) < $this->_gcProbability) {
            $latestExistsTime = time() - 60 * 60 * $this->_gcExportOutOfDateHours;
            $latestPrefix = date('ymd-Hi', $latestExistsTime);
            
            $flist = FileHelper::findFiles($this->tmpDir);
            foreach ($flist as $file) {
                // delete by storing time expired
                $fileTime = filectime($file);
                $cmpTime = $fileTime - $latestExistsTime;
                if ($cmpTime < 0) @unlink($file);

                // delete by filename analyse
                $filePrefix = substr(basename($file), 0, 11);
                $cmpPrefix = strncmp($filePrefix, $latestPrefix, 11);
                if ($cmpPrefix < 0) @unlink($file);
            }
        }
    }

    /**
     * Archivate item with $id.
     * @param integer $id
     * @param integer $page
     */
    public function actionExport($id, $page = 1)
    {
        $this->gc();

        $params = Yii::$app->request->getQueryParams();
        $paramSort = isset($params['sort']) ? $params['sort'] : null;

        //$model = $this->findModel($id); // exception if not found
        $model = $this->module->model('News')->findOne($id);
        if (empty($model)) {
            Yii::$app->session->setFlash('error', Yii::t($this->tcModule, "News id#{id} not found", ['id' => $id]));
        } else {
            $dir = $this->tmpDir . '/'. date('Y/md');
            FileHelper::createDirectory($dir);

            $slug = NewsArchieve::getSlug($model);

            $basename = $dir . '/' . sprintf("%s-news-%s-%s", date('ymd-His'), $id, $slug);
            if (is_file("{$basename}.zip")) $basename .= '.' . uniqid();
            $name = "{$basename}.zip";

            $result = NewsArchieve::createArchieve($name, $model, $this);
            if ($result === true) {
                //Yii::$app->session->setFlash('success', Yii::t($this->tcModule, 'Archieve create success'));
                return Yii::$app->response->sendFile($name);
            } else if (is_string($result)) {
                Yii::$app->session->setFlash('error', $result);
            } else {
                Yii::$app->session->setFlash('error', Yii::t($this->tcModule, 'Archieve creation fail by unknown error'));
            }
        }
        return $this->redirect(['index',
            'page' => $page,
            'id'   => $id,
            'sort' => $paramSort,
        ]);
    }

    /**
     * Archivate item with $id.
     */
    public function actionImport($page = 1)
    {
        $uploadModel = new NewsUpload;
        $uploadModel->module = $this->module;

        $id = 0;
        $uploadModel->archfile = UploadedFile::getInstance($uploadModel, 'archfile');

        if ($uploadModel->archfile && $uploadModel->validate()) {                
            $this->gc();

            $dir = $this->tmpDir . '/'. date('Y/md');
            FileHelper::createDirectory($dir);

            $filename = $dir . '/' . sprintf("%s-import-%s.%s"
                , date('ymd-His'), $uploadModel->archfile->baseName, $uploadModel->archfile->extension);
            $uploadModel->archfile->saveAs($filename);

            $model = $this->module->model('News');
            $result = NewsArchieve::extractArchieve($filename, $model, $this);
            if ($result === false) {
                $error1 = $model->firstErrors;
                $msg = array_shift($error1);
                Yii::$app->session->setFlash('error', Yii::t($this->tcModule, $msg));
            } else {
                $id = $model->id;
                Yii::$app->session->setFlash('success', Yii::t($this->tcModule
                    , 'Archieve imported successful - add new item #{id}', ['id' => $id]));
            }
        } else {
            if (!empty($uploadModel->errors['archfile'])) {
                Yii::$app->session->setFlash('error', $uploadModel->errors['archfile'][0]);
            } else {
                $error1 = $uploadModel->firstErrors;
                $msg = array_shift($error1);
                Yii::$app->session->setFlash('error', Yii::t($this->tcModule, $msg));
            }
        }

        return $this->redirect(['index',
            'page' => $page,
            'id'   => intval($id), // false -> 0
        ]);
    }

}
