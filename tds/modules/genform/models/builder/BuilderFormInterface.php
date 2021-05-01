<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 24.01.17
 * Time: 12:46
 */

namespace tds\modules\genform\models\builder;


use tds\modules\genform\models\fields\Fields;

interface BuilderFormInterface
{
    /**
     * Устанавливает модель полей на основе которой будет создана структура HTML формы.
     *
     * @param Fields $modelFields
     * @return null
     */
    public function setFields(Fields $modelFields);

    /**
     * Получить URL отправки формы.
     *
     * @return string
     */
    public function getAction();

    /**
     * Установить URL отправки формы.
     *
     * @param $action
     */
    public function setAction($action);

    /**
     * Получить опции формы.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Установить опции формы.
     *
     * @param $name
     * @param $value
     */
    public function setOptions($name, $value);

    /**
     * Получить заголовок формы.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Установить заголовок формы
     *
     * @param $title
     */
    public function setTitle($title);


    /**
     * Добавить скрытое поле у формы.
     *
     * @param $name
     * @param $value
     * @param array $options
     */
    public function addHiddenInput($name, $value, $options = []);

    /**
     * Удалить скрытое поле у формы
     *
     * @param $name
     * @return mixed
     */
    public function removeHiddenInput($name);

    /**
     * Добавить поле на указаной странице.
     *
     * @param $page
     * @param $location
     * @param null $type
     * @param null $name
     * @param array $options
     * @return void
     * @throws \Error
     */
    public function addField($page, $location, $type, $name = null, $options = []);

    /**
     * Удалить поле на указаной странице.
     *
     * @param $page
     * @param $location
     * @param $name
     * @return void
     * @throws \Error
     */
    public function deleteField($page, $location, $name);

    /**
     * Создание структури HTML формы, разделяя скрытые поля и контент формы.
     *
     * @return string
     */
    public function getForm();
}