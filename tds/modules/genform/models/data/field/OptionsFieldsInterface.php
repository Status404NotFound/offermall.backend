<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 08.02.17
 * Time: 15:25
 */

namespace tds\modules\genform\models\data\field;


interface OptionsFieldsInterface
{
    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param string $name
     * @param string|integer|boolean $value
     * @return void
     */
    public function addOption($name, $value);

    /**
     * @param string $name
     * @return string|integer|boolean
     */
    public function getOption($name);

    /**
     * @param string $class
     * @return void
     */
    public function addOptionClass($class);

    /**
     * @param string $class
     * @return void
     */
    public function deleteOptionClass($class);

    /**
     * @return string
     */
    public function getOptionsClass();

    /**
     * @param string $required
     * @return void
     */
    public function setOptionsRequired($required);

    /**
     * @return string
     */
    public function getOptionsRequired();

    /**
     * @param string $placeholder
     * @return void
     */
    public function setOptionsPlaceholder($placeholder);

    /**
     * @return string
     */
    public function getOptionsPlaceholder();

    /**
     * @param string $id
     * @return void
     */
    public function setOptionsId($id);

    /**
     * @return string
     */
    public function getOptionsId();
}