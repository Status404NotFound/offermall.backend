<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 16.01.17
 * Time: 17:34
 */

namespace tds\modules\genform\models\data\field;


use common\helpers\FishHelper;
use yii\base\Object;
use yii\helpers\Html;

/**
 * @property string optionsId
 * @property string type
 * @property string name
 * @property string|array value
 * @property boolean optionsRequired
 * @property string text
 * @property string optionsPlaceholder
 * @property array|string options
 * @property array params
 */

class DataField extends Object implements OptionsFieldsInterface, ParamFieldsInterface, EncodeDecodeDataInterface
{
    private $options = [];
    private $param = [];

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        throw new \Error('The option value is missing! Options key = ' . $name);
    }

    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function addOptionClass($class)
    {
        Html::addCssClass($this->options, $class);
    }

    public function deleteOptionClass($class)
    {
        Html::removeCssClass($this->options, $class);
    }

    public function getOptionsClass()
    {
        return $this->options['class'];
    }

    public function setOptionsRequired($required)
    {
        $this->options['required'] = $required;
    }

    public function getOptionsRequired()
    {
        return $this->options['required'];
    }

    public function setOptionsId($id)
    {
        $this->options['id'] = $id;
    }

    public function getOptionsId()
    {
        return $this->options['id'];
    }

    public function setOptionsPlaceholder($placeholder) {
        $this->options['placeholder'] = $placeholder;
    }

    public function getOptionsPlaceholder() {
        return $this->options['placeholder'];
    }

    public function setType($type) {
        $this->param['type'] = $type;
    }

    public function getType() {
        return $this->param['type'];
    }

    public function getName() {
        return $this->param['name'];
    }

    public function setName($name) {
        if (is_string($name)) {
            $this->param['name'] = $name;
        }
    }

    public function getValue() {
        return $this->param['value'];
    }

    public function setValue($value) {
        if (is_scalar($value)) {
            $this->param['value'] = $value;
        }
    }

    public function setText($text) {
        $this->param['text'] = $text;
    }

    public function getText() {
        return $this->param['text'];
    }

    public function setParams(array $params) {
        $this->param = $params;
    }

    public function getParams() {
        return $this->param;
    }

    public function getParam($name) {
        if (isset($this->param[$name])) {
            return $this->param[$name];
        }

        throw new \Error('The parameter value is missing! Parameter key = ' . $name);
    }

    public function addParam($name, $value) {
        $this->param[$name] = $value;
    }

    public function parseRequestData(array $data) {
        $param = $this->getParams();

        foreach ( $param as $key => &$value ) {
            if ( isset($data[$key]) ) {
                $value = $data[$key];
            }
        }

        if ( isset($data['values'])) {
            if ( $this->is_values_array($param['value']) ) {
                $param['value'] = [];
                foreach ( $data['values'] as $name => $text) {
                    array_push($param['value'],array(
                        'text'=> $text,
                        'options'=> [
                            'id' => $this->getOptionsId(),
                            'value' => $name
                        ]
                    ));
                }
            } else {
                $param['value'] = [];
                foreach ( $data['values'] as $valItem => $text) {
                    $param['value'][$valItem] = $text;
                }
            }
        }

        $this->setParams($param);

        return true;
    }

    private function is_values_array(array $array) {
        foreach ( $array as $value) {
            if ( is_array($value) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function encodeDataToArray()
    {
        return array('className' => self::className(),'options' => $this->getOptions(), 'params' => $this->getParams());
    }

    /**
     * @param array $data
     * @return void
     * @throws \Error
     */
    public function decodeArrayToData(array $data)
    {
        if (isset($data['className']) && self::className() === $data['className']) {
            $this->setOptions($data['options']);
            $this->setParams($data['params']);
        } else {
            throw new \Error('Array don\'t can be transformed to dataset fields');
        }
    }
}