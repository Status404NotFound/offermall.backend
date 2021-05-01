<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 16.01.17
 * Time: 17:34
 */

namespace tds\modules\genform\models;


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
 * @property array|string param
 */

class DataField extends Object
{
    //Array for fields
    private $options = [];
    private $param = [];

    public function getOptionsId() {
        return $this->options['id'];
    }

    public function getType() {
        return $this->param['type'];
    }

    public function getName() {
        return $this->param['name'];
    }

    public function getValue() {
        return $this->param['value'];
    }

    public function getText() {
        return $this->param['text'];
    }

    public function getOptionsRequired() {
        return $this->options['required'];
    }

    public function getOptionsPlaceholder() {
        return $this->options['placeholder'];
    }

    public function getOptions($name = null) {
        if (is_null($name)) {
            return $this->options;
        }

        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        throw new \Error('The option value is missing! Options value = ' . $name);
    }
    public function addOption($name, $value) {
        $this->options[$name] = $value;
    }

    public function getParam($name = null) {
        if (is_null($name)) {
            return $this->param;
        }

        if (isset($this->param[$name])) {
            return $this->param[$name];
        }

        throw new \Error('The parameter value is missing! Parameter value = ' . $name);
    }
    public function addParam($name, $value) {
        $this->param[$name] = $value;
    }

    public function addCssClass($class) {
        Html::addCssClass($this->options, $class);
    }

}