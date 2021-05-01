<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 06.02.17
 * Time: 17:51
 */

namespace tds\modules\genform\models\pages;


use tds\modules\genform\models\data\pages\DataPagesInterface;
use tds\modules\genform\models\builder\theme\Theme;

interface PageFormInterface
{
    /**
     * Возвращает страницы формы.
     *
     * @param DataPagesInterface $dataPages
     * @param Theme $themeFields
     * @return string
     */
    public function getPages(DataPagesInterface $dataPages, Theme $themeFields);

    /**
     * Возвращает страницу формы.
     *
     * @param DataPagesInterface $dataPages
     * @param $namePage
     * @param Theme $themeFields
     * @param bool $visible
     * @return string
     */
    public function getPage(DataPagesInterface $dataPages, $namePage, Theme $themeFields, $visible = true);

    /**
     * Возврат используемых скриптов.
     *
     * @return string
     */
    public function getJS();

    /**
     * Возврат используемых стилей
     *
     * @return string
     */
    public function getCSS();
}