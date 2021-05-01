<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 07.02.17
 * Time: 10:01
 */

namespace tds\modules\genform\models\data\pages;


use tds\modules\genform\models\builder\theme\ThemeInterface;

interface DataPagesInterface
{
    /**
     * Возврат массива с ключами масива данных, ключи соответствуют именам страниц формы.
     *
     * @return array
     */
    public function getNamePages();

    /**
     * @param string $name
     * @return array
     */
    public function getPage($name);

    /**
     * Возврат массива с данными для создания полей заголовка.
     *
     * @param $namePage
     * @return array
     */
    public function getHeader($namePage);

    /**
     * Возврат массива с данными для создания полей контента.
     *
     * @param $namePage
     * @return array
     */
    public function getContent($namePage);

    /**
     * Возврат массива с данными для создания полей подвала.
     *
     * @param $namePage
     * @return array
     */
    public function getFooter($namePage);

    /**
     * Добавить новое поле.
     *
     * @param $namePage
     * @param $section
     * @param ThemeInterface $themeFields
     * @param $type
     * @return \tds\modules\genform\models\data\field\DataField
     */
    public function addField($namePage, $section, ThemeInterface $themeFields, $type);

}