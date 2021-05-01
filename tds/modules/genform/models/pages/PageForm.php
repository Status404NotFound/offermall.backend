<?php

namespace tds\modules\genform\models\pages;


use tds\modules\genform\models\builder\theme\Theme;
use tds\modules\genform\models\data\pages\DataPagesInterface;
use yii\bootstrap\Html;

class PageForm implements PageFormInterface
{

    const GROUP_HEADER = 'header';
    const GROUP_CONTENT = 'content';
    const GROUP_FOOTER = 'footer';
    const GROUP_DEFAULT = 'default';
    /**
     * Возвращает страницы формы.
     *
     * @param  $dataPages
     * @param Theme $themeFields
     * @return string
     */
    public function getPages(DataPagesInterface $dataPages, Theme $themeFields)
    {
        $pages = '';
        $visible = true;

        $arrNamePages = $dataPages->getNamePages();
        foreach ($arrNamePages as $namePage) {

            $pages .= $this->getPage($dataPages, $namePage, $themeFields, $visible);
            $visible = false;
        }

        return Html::tag('div', $pages,['class' => 'wrap-pages']);
    }

    /**
     * Возвращает страницу формы.
     *
     * @param DataPagesInterface $dataPages
     * @param $namePage
     * @param Theme $themeFields
     * @param bool $visible
     * @return string
     */
    public function getPage(DataPagesInterface $dataPages, $namePage, Theme $themeFields, $visible = true)
    {
        $header = $this->getFields($dataPages->getHeader($namePage), $themeFields, PageForm::GROUP_HEADER);
        $content = $this->getFields($dataPages->getContent($namePage), $themeFields, PageForm::GROUP_CONTENT);
        $footer = $this->getFields($dataPages->getFooter($namePage), $themeFields, PageForm::GROUP_FOOTER);

        return Html::tag('div',
            Html::tag('div', $header, ['class' => 'form-header']) .
            Html::tag('div', $content, ['class' => 'form-content']) .
            Html::tag('div', $footer, ['class' => 'form-footer']),
            ['class' => 'pages page-' . $namePage, 'style' => $visible?'display:block':'display:none']
        );
    }

    /**
     * Возвращает поля, созданные на основе массива с данными DataField и указаной темой оформления.
     *
     * @param array $arrFields
     * @param Theme $themeFields
     * @param $group
     * @return string
     */
    protected function getFields(array $arrFields, Theme $themeFields, $group)
    {
        $htmlFields = '';
        foreach ($arrFields as $dataField) {
            $htmlFields .= $themeFields->getHTML($dataField, $group);
        }

        return $htmlFields;
    }

    /**
     * Возврат используемых скриптов.
     *
     * @return string
     */
    public function getJS()
    {
        return '';
    }

    /**
     * Возврат используемых стилей
     *
     * @return string
     */
    public function getCSS()
    {
        return '';
    }
}