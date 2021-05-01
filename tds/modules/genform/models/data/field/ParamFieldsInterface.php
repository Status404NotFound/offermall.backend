<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 08.02.17
 * Time: 16:07
 */

namespace tds\modules\genform\models\data\field;


interface ParamFieldsInterface
{
    /**
     * @param $type
     * @return void
     */
    public function setType($type);

    /**
     * @return string|integer
     */
    public function getType();

    /**
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string|integer|boolean $value
     * @return void
     */
    public function setValue($value);

    /**
     * @return string|integer|boolean
     */
    public function getValue();

    /**
     * @param string $text
     * @return void
     */
    public function setText($text);

    /**
     * @return string
     */
    public function getText();

    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params);

    /**
     * @return array
     */
    public function getParams();

    /**
     * @param string $name
     * @return mixed
     */
    public function getParam($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addParam($name, $value);
}