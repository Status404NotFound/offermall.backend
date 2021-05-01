<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 15.01.17
 * Time: 20:35
 */

namespace tds\modules\genform\models\builder;


use common\helpers\FishHelper;
use tds\modules\genform\models\DataField;
use tds\modules\genform\models\fields\Fields;
use yii\helpers\Html;

/**
 * @property  $modelFields Fields
 */
abstract class BuilderForm implements BuilderFormInterface
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

    private $formMethod = 'post';
    private $formAction = '';
    private $formOptions = ['class' => 'genform'];

    protected static $id_form = 0;
    protected static $title = 'Title';
    protected static $dataFields = [];

    /** @var Fields $modelFields */
    protected $modelFields;
    protected $pages = [];

    const TYPE_PAGE_FORM = 0;
    const TYPE_PAGE_THANK = 1;
    const TYPE_PAGE_CALCULATOR= 2;

    const FIELDS_CONTENT = 0;
    const FIELDS_FOOTER = 1;

    /**
     * BuilderForm constructor.
     * @param $id_form
     * @param Fields $modelFields
     */
    public function __construct($id_form, Fields $modelFields)
    {
        //Установка модели "отрисовки" полей
        $this->setFields($modelFields);
        //Определение страниц
        $this->pages[] = self::TYPE_PAGE_FORM;
        $this->pages[] = self::TYPE_PAGE_THANK;

        //Если данные уже были загружены
        if (self::$id_form === $id_form && $id_form > 0) {
            return null;
        }
        //Если идентификатор формы ноль - загружаем тестовые данные
        if ($id_form <= 0) {
            $this->addField(self::TYPE_PAGE_FORM,self::FIELDS_CONTENT,Fields::TYPE_TEXT_INPUT, 'name');
            $this->addField(self::TYPE_PAGE_FORM,self::FIELDS_CONTENT,Fields::TYPE_TELEPHONE, 'tell');
            $this->addField(self::TYPE_PAGE_FORM,self::FIELDS_CONTENT,Fields::TYPE_TEXTAREA, 'address');
            $this->addField(self::TYPE_PAGE_FORM,self::FIELDS_FOOTER,Fields::TYPE_BUTTON, 'button');

            $this->addField(self::TYPE_PAGE_THANK,self::FIELDS_CONTENT,Fields::TYPE_TEXT_BLOCK,'text');
            $this->addField(self::TYPE_PAGE_THANK,self::FIELDS_FOOTER,Fields::TYPE_BUTTON,'button');
//            $this->addField(self::TYPE_PAGE_FORM,self::FIELDS_FOOTER,Fields::TYPE_BUTTON,'btn',['class' => 'test']);

        } else {
            //TODO Получить данные с БД и проинициализировать форму
        }

//        $this->addButton(Fields::TYPE_BUTTON_SUBMIT, 'submit');

    }

    public function save()
    {
//        $data = [];
//        foreach (self::$dataFields as $page => $value) {
//            foreach ()
//            $data[$key]
//            Helper::debug(self::$dataFields);
//        }
    }


    /**
     * Установить модель полей
     * @param Fields $modelFields
     */
    public function setFields(Fields $modelFields) {
        $this->modelFields = $modelFields;
    }

    /**
     * Получить URL отправки формы
     * @return string
     */
    public function getAction() {
        return $this->formAction;
    }

    /**
     * Установить URL отправки формы
     * @param $action
     */
    public function setAction($action)
    {
        $this->formAction = $action;
    }

    /**
     * Получить опции формы
     * @return array
     */
    public function getOptions() {
        return $this->formOptions;
    }

    /**
     * Установить опции формы
     * @param $name
     * @param $value
     */
    public function setOptions($name, $value)
    {
        if ( isset($this->formOptions[$name]) ) {
            //TODO прологировать/предупредить перезапись значений
        } else {
            $this->formOptions[$name] = $value;
        }
    }

    /**
     * Получить заголовок формы
     * @return string
     */
    public function getTitle() {
        return self::$title;
    }

    /**
     * Установить заголовок формы
     * @param $title
     */
    public function setTitle($title) {
        self::$title = $title;
    }

    /**
     * Добавить скрытые поля формы
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

    /**
     * Удалить скрытое поле у формы
     *
     * @param $name
     * @return void
     */
    public function removeHiddenInput($name) {
        if (isset($this->dataHiddenInputs[$name]) ) {
            unset($this->dataHiddenInputs[$name]);
        }
    }

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
    public function addField($page, $location, $type, $name = null, $options = [])
    {
        //Когданибуть найти решение по лучше!
        if ($location === self::FIELDS_CONTENT && $location === self::FIELDS_FOOTER) {
            throw new \Error('Location value is incorrect (' . $location . ')');
        }

        /** @var DataField $paramFields */
        $paramFields = $this->modelFields->getDefaultValue($type);

        foreach ($options as $nameOptions => $option) {
            $paramFields->addOption($nameOptions,$option);
        }

        //Добавить в данные имя поля
        if ($paramFields->optionsId == null) {
            if (in_array($name, self::$dataFields)) {
                throw new \Error('Имя поля не может повторятся! Если нужно переписать поле то сначала удалите имеющейся поле');
            }
            $paramFields->optionsId = $name;
        }

        //Добавить поле к остальным полям
        self::$dataFields[$page][$location][$paramFields->optionsId] = $paramFields;
    }

    /**
     * Удалить поле на указаной странице.
     *
     * @param $page
     * @param $location
     * @param $name
     * @return void
     * @throws \Error
     */
    public function deleteField($page, $location, $name)
    {
        //Когданибуть найти решение по лучше!
        if ($location === self::FIELDS_FOOTER && $location === self::FIELDS_FOOTER) {
            throw new \Error('Location value is incorrect (' . $location . ')');
        }

        if (isset(self::$dataFields[$page][self::FIELDS_CONTENT][$name])) {
            unset(self::$dataFields[$page][self::FIELDS_CONTENT][$name]);
        } else {
            throw new \Error('Deleting is not possible, because the name does not exist ' . $name);
        }
    }

    /**
     * Создание структури HTML формы, разделяя скрытые поля и контент формы.
     *
     * @return string
     */
    public function getForm() {
//        Helper::debug(self::$dataFields);
        $form = Html::beginForm($this->getAction(),$this->formMethod,$this->getOptions());
        $form .= $this->hiddenInput();
        $form .= $this->pages();
        $form .= Html::endForm();

        return $form;
    }



    /**
     * Создание скритых полей на основе массива опций.
     *
     * @return string
     */
    protected function hiddenInput()
    {
        $html = '';
        foreach ($this->dataHiddenInputs as $nameInput => $paramInput) {
            $html .= Html::hiddenInput($nameInput, $paramInput['value'], $paramInput['options']) . PHP_EOL;
        }
        return $html;
    }

    protected function getNamePages($page = null) {
        if (!is_null($page)) {
            return $this->getNamePages()[$page];
        }

        return [
            self::TYPE_PAGE_FORM => 'form',
            self::TYPE_PAGE_THANK => 'thank',
            self::TYPE_PAGE_CALCULATOR => 'calculator',
        ];
    }

    /**
     * Создания заголовка формы и обертки страниц формы с страницами
     * @return string
     */
    protected function pages() {
        $header = Html::tag('div', $this->headerForm(), ['class' => 'form-header']);
        //Постраничное разделение
        $pages = '';
        $fistHidden = 'display: block;';
        foreach ($this->pages as $page) {
            if (isset(self::$dataFields[$page])) {
                $pages .= Html::tag('div',
                    $this->getPage(self::$dataFields[$page]),
                    ['class' => 'pages page-' . $this->getNamePages($page), 'style' => $fistHidden]
                );
            } else {
                $pages .= Html::tag('div',
                    $this->getPage([]),
                    ['class' => 'pages page-' . $this->getNamePages($page), 'style' => $fistHidden]
                );
            }
            $fistHidden = 'display: none;';
        }

        return $header . PHP_EOL . Html::tag('div',
            $pages . PHP_EOL . PHP_EOL
            ,['class' => 'wrap-pages']
        );
    }

    /**
     * Создание заголовка формы на основе модели темы.
     *
     * @return string
     */
    protected function headerForm()
    {
//        return $this->modelFields->getHeaderForm(self::$title);
        return $this->modelFields->getDefaultHTML(Fields::TYPE_HEADER_TITLE);
    }

    /**
     * Создание контента страниц формы.
     *
     * @param $page
     * @return string
     */
    protected function getPage($page)
    {
        $contentHTML = '';
        $footerHTML = '';

        if (isset($page[self::FIELDS_CONTENT])) {
            $contentHTML = $this->getField($page[self::FIELDS_CONTENT]);
        }

        if (isset($page[self::FIELDS_FOOTER])) {
            $footerHTML = $this->getField($page[self::FIELDS_FOOTER]);
        }

        return
            Html::tag('div', $contentHTML, ['class' => 'form-content']) .
            Html::tag('div',null, ['class' => 'clearfix']) .
                Html::tag('div', $footerHTML, ['class' => 'form-footer']);
    }

    protected function getField($fields) {
        $fieldsHTML = '';
        foreach ($fields as $nameField => $field) {
            $fieldsHTML .= $this->modelFields->getHTML($field);
        }

        return $fieldsHTML;
    }

    public function getCSS() {
        $css =  $this->modelFields->getCSS();
        $css .= '
        /* *** form-style *** */
        form.genform {
            max-width: 400px;
            margin: 0 auto;
            font-size: 13px!important;
            position: relative;
            font-family: Helvetica,Arial,sans-serif!important;
            background-color: #ffffff;
            border: 1px solid #999!important;
            border-image-source: initial;
            border-image-slice: initial;
            border-image-width: initial;
            border-image-outset: initial;
            border-image-repeat: initial;
            -webkit-border-radius: 10px 10px 6px 6px!important;
            -moz-border-radius: 10px 10px 6px 6px!important;
            border-radius: 10px 10px 6px 6px!important;
            outline: 0!important;
            -webkit-box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
            -moz-box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
            box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
            -webkit-background-clip: padding-box!important;
            -moz-background-clip: padding-box!important;
            background-clip: padding-box!important;
            text-align: left;
            color: #444;
            background-color: #f1f1f1;
        }
        /* *** end *** */
        
        /* *** form-header-style *** */
        .genform .form-header {
            color: #fff;
            background-color: #2c3e50;
            border-bottom: 0;
            padding: 9px 15px;
            border-top-right-radius: 6px;
            border-top-left-radius: 6px;
        }
        .genform .form-header h3 {
            margin: 0!important;
            text-align: center;
            line-height: 30px;
            font-size: 28px;
            font-weight: 700;
        }
        /* *** end *** */
        
        /* *** form-content-style *** */
        .genform .form-content {
            min-height: 50px;
            padding-top: 10px;
            background-color: #fff;
        }
        /* *** end *** */
        ';
        return $css;
    }

    public function getScript() {
        return '';
    }
}