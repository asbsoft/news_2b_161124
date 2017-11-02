<?php

namespace asb\yii2\modules\news_2b_161124\models;

use asb\yii2\modules\news_2b_161124\Module;

use asb\yii2\modules\news_1b_160430\models\News as BaseNews;

class News extends BaseNews
{
    public $slug;

    /**
     * Get slug of this model for selected language
     * @param string $langCode
     * @return string|false
     */
    public function getSlug($langCode)
    {
        $slug = false;
        foreach ($this->i18n as $i18nModel) {
            if ($i18nModel->lang_code == $langCode) {
                $slug = $i18nModel->slug;
                break;
            }
        }
        return $slug;
    }
}
