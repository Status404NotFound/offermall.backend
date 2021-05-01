<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 17.03.17
 * Time: 17:43
 */

namespace common\theme\inspinia\widgets;


use common\theme\inspinia\Select2Asset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

class Select2 extends InputWidget {/**
 * @var array listbox items
 */
    public $items = [];

    /**
     * @var string|array selected items
     */
    public $selection;

    /**
     * @var array listbox options
     */
    public $options = [];

    /**
     * @var array dual listbox options
     */
    public $pluginOptions = [];

    public function init()
    {
        parent::init();

        $this->pluginOptions['data'] = [];
    }

    public function run() {

        $data = &$this->pluginOptions['data'];
        foreach ( $this->items as $key => $value ) {
            array_push($data, array('id' => strtolower($key), 'text' => $value));
        }
        $this->items = [];

        if ((array_key_exists('multiple', $this->options))) {
            $this->pluginOptions['multiple'] = $this->options['multiple'];
            unset($this->options['multiple']);
        }

        $this->registerAssets();

        Html::addCssClass($this->options, 'form-control');

        $this->items = [];

        if ($this->hasModel()) {
            return Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            return Html::dropDownList($this->name, $this->selection, $this->items, $this->options);
        }
    }

    /**
     * Registers Assets
     */
    public function registerAssets() {
        $view = $this->getView();
        Select2Asset::register($view);

        $id = (array_key_exists('id', $this->options)) ? $this->options['id'] : Html::getInputId($this->model, $this->attribute);
        $options = Json::encode($this->pluginOptions);

        $js = "jQuery('#{$id}').select2($options)";
        $view->registerJs("$js;");
    }
}
