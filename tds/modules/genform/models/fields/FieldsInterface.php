<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 18.01.17
 * Time: 9:06
 */

namespace tds\modules\genform\models\fields;


use tds\modules\genform\models\DataField;

interface FieldsInterface
{
    /**
     * Получить название типа "Title" по константе типа или массив всех "Title" значений.
     *
     * @param null|integer $type
     * @return array|string
     */
    public static function getTypeName($type = null);

    /**
     * Получить обьект DataField c значениями по умолчанию которые неоходимые.
     *
     * для создания поля указаного типа
     * @param integer $type
     * @return DataField
     */
    public function getDefaultValue($type);

    /**
     * Получить HTML код поля указаного типа которое было создано на основе DataField значений по умолчанию.
     *
     * @param string $type
     * @return string HTML
     */
    public function getDefaultHTML($type);

    /**
     * Получить HTML код поля которое было создано на основе DataField значений.
     *
     * @param DataField $dataField
     * @return string
     */
    public function getHTML(DataField $dataField);

    /**
     * Получить код таблицы стилей для созданных полей.
     *
     * @return string
     */
    public function getCSS();

    /**
     * Получить код скриптов для полей.
     *
     * @return string
     */
    public function getJS();
}