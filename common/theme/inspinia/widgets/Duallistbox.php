<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 05.04.17
 * Time: 12:31
 */

namespace common\theme\inspinia\widgets;


use common\theme\inspinia\DuallistboxAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class Duallistbox extends InputWidget {
    /**
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
    public $pluginOptions;

    public function run()
    {
        $this->registerAssets();

        Html::addCssClass($this->options, 'form-control');
        $this->options['multiple'] = true;

        if ($this->hasModel()) {
            return Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);
        } else {
            return Html::listBox($this->name, $this->selection, $this->items, $this->options);
        }
    }

    /**
     * Registers Assets
     */
    public function registerAssets() {
        $view = $this->getView();
        DuallistboxAsset::register($view);

        $id = (array_key_exists('id', $this->options)) ? $this->options['id'] : Html::getInputId($this->model, $this->attribute);
        $options = Json::encode($this->pluginOptions);

        $js = "jQuery('#{$id}').select2($options)";
        $view->registerJs("$js;");
    }
}