<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 25.01.17
 * Time: 12:02
 */

namespace tds\modules\genform\models\fields;


use tds\modules\genform\models\DataField;

interface FieldsWrapInterface
{
    /**
     * Получить массив опций для обертки поля.
     *
     * @param DataField $dataField
     * @return array
     */
    public function getOptionsWrapFields(DataField $dataField);

    /**
     * Добавить массив опций для обертки поля.
     *
     * @param array $optionsWrap
     */
    public function setOptionsWrapFields(array $optionsWrap);

    /**
     * Вставить строку после поля.
     *
     * @param string $afterField
     * @throws \Error
     */
    public function setAfterField($afterField);

    /**
     * Вставить строку перед полем.
     *
     * @param string $beforeField
     * @throws \Error
     */
    public function setBeforeField($beforeField);

}