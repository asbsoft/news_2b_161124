<?php

namespace asb\yii2\modules\news_2b_161124\models;

//use asb\yii2\common_2_170212\helpers\EditorContentHelper; // better use as container-module's members
//use asb\yii2\common_2_170212\i18n\LangHelper;

use asb\yii2\modules\news_2b_161124\Module;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;

use ZipArchive;

class NewsArchieve
{
    const START_TEXT_TAG = "\n<!-- BEGIN -->\n";
    const END_TEXT_TAG   = "\n<!-- END -->\n";

    public static $localFilesSubdir = '@files';

    public static $attributesFilename = 'attributes.htm';

    public static $slugMaxLen = 100;

    /**
     * Create Zip-archieve conteins news data and images.
     * @param string $name
     * @param News $model
     * @param Controller $controller
     * @return true|string error message
     */
    public static function createArchieve($name, $model, $controller)
    {
        $module = Module::getModuleByClassname(Module::className());
        $tc = $module->tcModule;
        $uploadsDir = Yii::getAlias($module->params['uploadsNewsDir']);
        $editorContentHelper = $module->contentHelper;

        $attributes = $model->attributes;

        $subdir = $model->getImageSubdir($model->id);
        $imgdir = FileHelper::normalizePath($uploadsDir . '/' . $subdir, '/');

        $files = [];
        if (!empty($attributes['image'])) {
            $files[] = FileHelper::normalizePath($uploadsDir . '/' . $attributes['image'], '/');
            $attributes['image'] = strtr($attributes['image'], [$subdir => static::$localFilesSubdir]);
        }

        $texts = [];
        $i18nModels = $model->i18n;
        foreach ($i18nModels as $i18nModel) {
            if (!empty($i18nModel)) {
                $i18nAttributes = $i18nModel->attributes;
                $texts[$i18nModel->lang_code] = static::preSaveText($i18nAttributes['body'], $controller, [
                    $editorContentHelper::uploadUrl($imgdir, '@uploadspath', '@webfilesurl') => static::$localFilesSubdir
                ]);
                unset($i18nAttributes['body'], $i18nAttributes['news_id'], $i18nAttributes['lang_code']);
                $attributes[$i18nModel->lang_code] = $i18nAttributes;
            }
        }

        $flist = [];
        if (is_dir($imgdir)) {
             $flist = FileHelper::findFiles($imgdir, ['filter' => function($path) {
                 if (substr(basename($path), 0, 1) == '.') return false; // skip '.files'
                 return true;
             }]);
        }
        foreach ($flist as $file) {
            $file = FileHelper::normalizePath($file, '/');
            if (!in_array($file, $files)) {
                $files[] = $file;
            }
        }

        $zip = new ZipArchive();
        if ($zip->open($name, ZipArchive::CREATE) !== true) {
            return Yii::t($tc, "Can't create archieve '{name}'", ['name' => $name]);
        }

        foreach ($files as $file) {
            $basePath = strtr($file, [$imgdir => '']);
            $relativePath = static::$localFilesSubdir . $basePath;
            $zip->addFile($file, $relativePath);
        }

        $json = Json::encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $zip->addFromString(static::$attributesFilename, $json);

        foreach ($texts as $langCode => $text) {
            if (!empty($text)) {
                $localFname = "body[{$langCode}].htm";
                $zip->addFromString($localFname, $text);
            }
        }

        $zip->close();

        return true;
    }

    /**
     * Processing text before saving.
     * @param string $text
     * @param Controller $controller
     * @param array $transTable
     * @return string
     */
    public static function preSaveText($text, $controller, $transTable = [])
    {
        $module = Module::getModuleByClassname(Module::className());
        $editorContentHelper = $module->contentHelper;

        $text = trim($text);
        if (empty($text)) return '';
        
        $text = $editorContentHelper::afterSelectBody($text); // convert @uploads/.../ID to realpath

        $text = strtr($text, $transTable);

        $html = $text;
        $viewFile = dirname(__DIR__) . '/views/news-archieve-text.php';
        if (is_file($viewFile)) {
            $text = static::START_TEXT_TAG . $text . static::END_TEXT_TAG;
            $html = Yii::$app->view->renderFile($viewFile, ['text' => $text], $controller);
        }
        return $html;
    }

    /**
     * Get first non-empty slug of news. Search in default languages priority.
     * @param News $model
     * @return string
     */
    public static function getSlug($model)
    {
        $i18nModels = $model->i18n;
        $slugs = [];
        foreach ($i18nModels as $i18nModel) {
            if (empty($i18nModel->body)) continue; // skip languages for news without body
            $slugs[$i18nModel->lang_code] = $i18nModel->slug;
        }

        //$langs = array_keys(LangHelper::activeLanguages()); // better use as container-module's member:
        $module = Module::getModuleByClassname(Module::className());
        $langHelper = $module->langHelper;
        $langs = array_keys($langHelper::activeLanguages());
        $slug = '';
        foreach ($langs as $lang) {
            if (!empty($slugs[$lang])) {
                $slug = $slugs[$lang];
                break;
            }
        }
        if (strlen($slug) > static::$slugMaxLen) {
            $slug = substr($slug, 0, static::$slugMaxLen);
        }
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Extract Zip-archieve.
     * @param string $filename
     * @param News $model
     * @param Controller $controller
     * @return boolean and on fail set $model->errors[]
     */
    public static function extractArchieve($filename, $model, $controller)
    {
        $module = Module::getModuleByClassname(Module::className());
        $tc = $module->tcModule;
        $uploadsDir = Yii::getAlias($module->params['uploadsNewsDir']);
        $editorContentHelper = $module->contentHelper;

        $zip = new ZipArchive();
        $errcode = $zip->open($filename);
        if ($errcode !== true) {
            if ($errcode == ZIPARCHIVE::ER_INCONS || $errcode == ZIPARCHIVE::ER_NOZIP) {
                $errmsg = Yii::t($tc, 'Illegal Archieve format');
            } else {
                $errmsg = Yii::t($tc, "Can't open archieve (error code '{errcode}')", ['errcode' => $errcode]);
            }
            $model->addError('*', $errmsg);
            return false;
        }

        $attributes = $zip->getFromName(static::$attributesFilename);
        if ($attributes === false) {
            $model->addError('*', Yii::t($tc, "Attributes file '{filename}' not found in archieve"
                , ['filename' => static::$attributesFilename]));
            return false;
        }
        $attributes = Json::decode($attributes);

        unset($attributes['id']);
        //unset($attributes['create_time']); // can't redefine - will set now
        unset($attributes['update_time'], $attributes['show_from_time'], $attributes['show_to_time']);
        $attributes['is_visible'] = false;
        $attributes['owner_id'] = Yii::$app->user->id; // original owner id may not be in this system
        $model->load($attributes, '');

        $transaction = $model::getDb()->beginTransaction();
        try {

            if (!$model->save()) { // draft save for get news_id
                return false;
            }

            // correct path for title image
            $subdir = $model->getImageSubdir($model->id);
            //$imgdir = $uploadsDir . '/' . $subdir;
            if (!empty($attributes['image'])) {
                $model->image = strtr($attributes['image'], [static::$localFilesSubdir => $subdir]);
            }
            $model->save(false, ['image']);

            // multilang part
            $subdir = $model->getImageSubdir($model->id);
            $imgdir = $uploadsDir . '/' . $subdir;
            $transTable = [static::$localFilesSubdir => $editorContentHelper::uploadUrl($imgdir, '@uploadspath', '@webfilesurl')];
            $modelsI18n = $model->prepareI18nModels($model);
            foreach ($modelsI18n as $langCode => $modelI18n) {
                // import only data for languages exist in this system
                if (isset($attributes[$langCode]) && is_array($attributes[$langCode])) {
                    unset($attributes[$langCode]['id']);
                    $attributes[$langCode]['news_id'] = $model->id;

                    $existsSlug = $modelI18n->findOne(['slug' => $attributes[$langCode]['slug']]);
                    if ($existsSlug) unset($attributes[$langCode]['slug']); // if slug already exists will generate new slug

                    $body = (string) $zip->getFromName("body[{$langCode}].htm"); // false -> ''
                    $attributes[$langCode]['body'] = static::preLoadText($body, $transTable);

                    $modelI18n->load($attributes[$langCode], '');
                    $resultI18n = $modelI18n->save();
                    if (!$resultI18n) {
                        $transaction->rollBack();
                        $error1 = $modelI18n->firstErrors;
                        $msg = array_shift($error1);
                        $model->addError('*', $msg);
                        return false;
                    }
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $model->addError('*', $e->getMessage());
            return false;
        }

        // load images
        if (isset($imgdir)) {
            //FileHelper::createDirectory($imgdir); // archieve may not have images
            $saved = true;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, static::$localFilesSubdir) === 0) {
                    $subpath = substr($filename, strlen(static::$localFilesSubdir));
                    $path = $imgdir . $subpath;
                    $filebody = $zip->getFromName($filename);
                    if ($filebody) {
                        FileHelper::createDirectory(dirname($path));
                        $result = @file_put_contents($path, $filebody);
                        if ($result === false) {
                            $saved = false;
                            $model->addError('*', Yii::t($tc, "Can't save at least one of embedded files of archieve"));
                        }
                    }
                }
            }
            if (!$saved) return false;
        }
        $zip->close();
        return true;
    }

    /**
     * Correct news body text:
     * - save only text between start and end tags
     * - correct path to embed files according to $transTable
     * @param string $text
     * @param array $transTable
     * @return string
     */
    public static function preLoadText($text, $transTable = [])
    {
        $module = Module::getModuleByClassname(Module::className());
        $editorContentHelper = $module->contentHelper;

        $text = trim($text);
        if (empty($text)) return '';

        // exstract text between start and end tags
        $pos = strpos($text, static::START_TEXT_TAG);
        if ($pos !== false) {
            $pos += strlen(static::START_TEXT_TAG);
            $text = substr($text, $pos);
        }
        $pos = strpos($text, static::END_TEXT_TAG);
        if ($pos !== false) {
            $text = substr($text, 0, $pos);
        }

        // correct files path
        $text = strtr($text, $transTable);
        $text = $editorContentHelper::beforeSaveBody($text); // convert realpath to '@uploads/.../ID'

        //var_dump($text);exit;
        return $text;
    }

}
