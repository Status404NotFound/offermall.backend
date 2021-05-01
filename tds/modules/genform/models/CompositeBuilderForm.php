<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 15.01.17
 * Time: 20:35
 */

namespace tds\modules\genform\models;


use common\helpers\FishHelper;
use yii\helpers\Html;

/**
 * @property  $modelFields Fields
 */
abstract class BuilderForm
{
    /**
     * Example:
     * ...
     * 'nameOptionsTag' => [
     *      'value' => 'DefaultValue',
     *      'options' => [
     *          'id' => 'DefaultID',
     *          'class' => 'DefaultClass'
     *      ]
     * ]
     * ...
     */
    private $dataHiddenInputs = [
        'ip' => [
            'value' => 'ip_form',
            'options' => [
                'class' => ''
            ],
        ],
        'cookie' => [
            'value' => '',
            'options' => [
                'class' => 'adfsh-ck'
            ],
        ],
        'view_hash' => [
            'value' => '',
            'options' => [
                'class' => 'orderViewHash'
            ],
        ],
        'sid' => [
            'value' => 'sid_form',
            'options' => [
                'class' => ''
            ],
        ]
    ];
    private $method = 'post';
    private $action = '';
    private $options = [];
    private $id_form = 0;
    private $title = 'Title';
    private $contentFields = [];
    private $footerFields = [];
    private $modelFields;


    /**
     * BuilderForm constructor.
     * @param $id_form
     * @param Fields $modelFields
     */
    public function __construct($id_form, Fields $modelFields)
    {
        
        if ($id_form <= 0) {
            //TODO Загрузить тэстовые данные
        }
        if ($this->id_form === $id_form) {
            //TODO данные уже были загружены
        }
        //TODO Получить данные с БД и проинициализировать форму
        
        //TODO установить тип полей
        $this->setFields($modelFields);
    }

    /**
     * @param Fields $modelFields
     */
    public function setFields(Fields $modelFields) {
        $this->modelFields = $modelFields;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->action;
    }

    /**
     * @param $method
     */
    public function setMethod($method)
    {
        if (strnatcasecmp('post', $method) || strnatcasecmp('get', $method)) {
            //TODO прологировать/предупредить
        }
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOptions($name, $value)
    {
        if ( isset($this->options[$name]) ) {
            //TODO прологировать/предупредить перезапись значений
        } else {
            $this->options[$name] = $value;
        }
    }

    /**
     * @param $name
     * @param $value
     * @param array $options
     */
    public function addHiddenInput($name, $value, $options = []) {
        if ( isset($this->dataHiddenInputs[$name]) ) {
            //TODO прологировать/предупредить перезапись значений
        }
        $this->dataHiddenInputs[$name] = ['value' => $value, 'options' => $options];
    }

    public function addContentFields($param) {
        //TODO required $param['type']!!!
        if (isset($param['type'])) {

        }
        $paramFields = $this->modelFields->getParam($param['type']);

    }

    /**
     * @return string
     */
    public function renderHTML() {
        $form = Html::beginForm($this->getAction(),$this->getMethod(),$this->getOptions());
        $form .= $this->hiddenInput();
        $form .= $this->content();
        $form .= Html::endForm();

        return $form;
    }

    /**
     * @return string
     */
    private function hiddenInput()
    {
        $html = '';
        foreach ($this->dataHiddenInputs as $nameInput => $paramInput) {
            $html .= Html::hiddenInput($nameInput, $paramInput['value'], $paramInput['options']);
        }
        return $html;
    }

    protected function content() {
        $header = Html::tag('div', $this->headerForm(), ['class' => 'form-header']);
        $content = Html::tag('div', $this->contentForm(), ['class' => 'form-content']);
        $footer = Html::tag('div', $this->footerForm(), ['class' => 'form-footer']);

        return Html::tag('div',
            $header . PHP_EOL .
            $content . PHP_EOL .
            $footer . PHP_EOL
            ,['class' => 'content']);
    }

    /**
     * @return string
     */
    protected function headerForm()
    {
        return Html::tag('h3', $this->title);
    }

    /**
     * @return string
     */
    protected function contentForm()
    {
        $html = '';
        foreach ($this->contentFields as $field) {
            $html .= $this->modelFields->renderHTML($field);
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function footerForm()
    {
        $html = '';
        foreach ($this->footerFields as $field) {
            $html .= $this->modelFields->renderHTML($field);
        }

        return $html;
    }
}