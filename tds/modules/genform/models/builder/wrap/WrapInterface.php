<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 25.01.17
 * Time: 12:02
 */

namespace tds\modules\genform\models\builder\wrap;


use tds\modules\genform\models\data\field\DataField;

interface WrapInterface
{
    /**
     * Добавить массив опций для обертки поля.
     *
     * @param array $optionsWrap
     * @param $group
     * @return
     */
    public function setOptionsWrapFields(array $optionsWrap, $group);

    /**
     * Получить массив опций для обертки поля.
     *
     * @param DataField $dataField
     * @param $group
     * @return array
     */
    public function getOptionsWrapFields(DataField $dataField, $group);

    /**
     * Вставить строку после поля.
     *
     * @param string $afterField
     * @param $group
     * @return
     */
    public function setAfterField($afterField, $group);

    /**
     * Получить строку для вставки после поля.
     *
     * @param DataField $dataField
     * @param $group
     * @return string
     */
    public function getAfterField(DataField $dataField, $group);

    /**
     * Вставить строку перед полем.
     *
     * @param string $beforeField
     * @param $group
     * @return
     */
    public function setBeforeField($beforeField, $group);

    /**
     * Получить строку для вставки перед полем.
     *
     * @param DataField $dataField
     * @param $group
     * @return string
     */
    public function getBeforeField(DataField $dataField, $group);
}