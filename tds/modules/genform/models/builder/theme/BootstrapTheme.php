<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 17.01.17
 * Time: 22:07
 */

namespace tds\modules\genform\models\builder\theme;


use tds\modules\genform\models\data\field\DataField;
use tds\modules\genform\models\pages\PageForm;
use yii\bootstrap\Html;

class BootstrapTheme extends Theme
{

    static $countField = 0;

    /**
     * Подготовка и установка первоначальных данных
     */
    public function init()
    {
        $this->setOptionsWrapFields(['class' => 'form-group'],PageForm::GROUP_CONTENT);
        $this->setOptionsWrapFields(['class' => 'form-group'],PageForm::GROUP_FOOTER);
        $this->setOptionsWrapFields(['class' => 'form-group'],PageForm::GROUP_DEFAULT);

        $this->setAfterField(Html::tag('div', null, ['class' => 'clearfix']), PageForm::GROUP_CONTENT);
        $this->setAfterField(Html::tag('div', null, ['class' => 'clearfix']), PageForm::GROUP_DEFAULT);
    }

    /**
     * Получить обьект DataField c значениями по умолчанию которые неоходимые.
     *
     * @param int $type
     * @return DataField
     */
    public function getDefaultValue($type)
    {
        $name = str_replace(' ', '_',strtolower(Theme::getTypeName($type)))  . '-' . self::$countField++;

        $dataField = new DataField();

        $dataField->addOption('id', $name);
        $dataField->addParam('type', $type);
        $dataField->addParam('value', null);

        switch ($type):
            case Theme::TYPE_HIDDEN_INPUT:
                $dataField->addParam('name', $name);
                $dataField->addParam('value', 'hidden');
                break;
            case Theme::TYPE_TEXT_INPUT:
            case Theme::TYPE_NUMBER_INPUT:
            case Theme::TYPE_TEXTAREA:
            case Theme::TYPE_EMAIL:
            case Theme::TYPE_TELEPHONE:
                $dataField->addParam('name', $name);
                $dataField->addOption('required', false);
                $dataField->addOption('placeholder', 'Placeholder');
                $dataField->addParam('textLabel', Theme::getTypeName($type));
                $dataField->addParam('textHelp', Theme::getTypeName($type) . ' help text');
                break;
            case Theme::TYPE_RADIO_HORIZONTAL:
            case Theme::TYPE_RADIO_VERTICAL:
            case Theme::TYPE_CHECK_HORIZONTAL:
            case Theme::TYPE_CHECK_VERTICAL:
                $dataField->addParam('name', $name);
                $dataField->addOption('required', false);
                $dataField->addParam('textLabel', Theme::getTypeName($type));
                $dataField->addParam('textHelp', Theme::getTypeName($type) . ' help text');
                $dataField->addParam('value', [
                    ['text' => 'test1', 'options'=> ['id' => $name, 'value' => '', ]],
                    ['text' => 'test2', 'options'=> ['id' => $name, 'value' => '', ]],
                    ['text' => 'test3', 'options'=> ['id' => $name, 'value' => '', ]],
                ]);
                break;
            case Theme::TYPE_SELECT:
            case Theme::TYPE_SELECT_MULTIPLE:
                $dataField->addParam('name', $name);
                $dataField->addOption('required', false);
                $dataField->addParam('textLabel', Theme::getTypeName($type));
                $dataField->addParam('textHelp', Theme::getTypeName($type) . ' help text');
                $dataField->addParam('value', [
                    //value => text
                    0 => 'test1',
                    1 => 'test2',
                    2 => 'test3',
                ]);
                break;
            case self::TYPE_TEXT_BLOCK:
                $dataField->addParam('text', 'Text block');
                break;
            case self::TYPE_LINK:
                $dataField->addParam('text', 'link');
                break;
            case self::TYPE_BUTTON:
                $dataField->addParam('text', 'button');
                break;
            case self::TYPE_BUTTON_INPUT:
                $dataField->addParam('text', 'button input');
                break;
            case self::TYPE_BUTTON_SUBMIT:
                $dataField->addParam('text', 'button submit');
                break;
            case self::TYPE_HEADER_TITLE:
                $dataField->addParam('title', 'Title');

                break;
        endswitch;

        return $dataField;
    }

    /**
     * Получить скрипты
     *
     * @return string
     */
    public function getJS()
    {
        return '';
    }

    /**
     * Получить код таблицы стилей для созданных полей.
     *
     * @return string
     */
    public function getCSS()
    {
        $path = \Yii::getAlias('@genform/web/css/fields/bootstrap.css');
        return implode(PHP_EOL, file($path)) .
            <<<'CSS'

label {
    text-align: right;
}
.form-group {
    position: relative;
    user-select: none;
}
.form-group * {
    user-select: none;
    cursor: default;
}
.form-group .btn {
    width: 60% !important;
    margin: 0 20%;
}
.list-group-item {
    padding: 15px 0 0 0;
}
            
CSS;
    }

    /**
     *  Array elements = [
     *      ...
     *      [
     *          'id' => '', //required
     *          'value' => '',
     *          'checked' => '',
     *      ],
     *      ...
     *  ]
     * @param string $type
     * @param string $name
     * @param array $elements
     * @param array $optionsLabel
     * @param bool $inline
     * @return string
     */
    private function renderRC($type, $name, $elements, $optionsLabel = null, $inline = true) {
        $html = '';
        $countElem = 0;

        if ($inline) {
            foreach ($elements as $element) :
                $options = $element['options'];
                $options['id'] .= '-' . $countElem++;

                $html .= Html::tag('div',
                    Html::label(
                        Html::input($type, $name, null, $options) . $element['text'],
                        $options['id'],
                        $optionsLabel
                    )
                    , ['class' => $type]
                );
            endforeach;
        } else {
            foreach ($elements as $element) :
                $options = $element['options'];
                $options['id'] .= '-' . $countElem++;

                $html .= Html::label(
                    Html::input($type, $name, null, $options) . $element['text'],
                    $options['id'],
                    $optionsLabel
                );
            endforeach;
        }

        return $html;
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getHiddenInput(DataField $dataField) {
        return Html::hiddenInput($dataField->name, $dataField->value, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTextInput(DataField $dataField) {
        $dataField->addOptionClass('form-control');
        $input = Html::input('text', $dataField->name, $dataField->value, $dataField->options );
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getNumberInput(DataField $dataField) {
        $dataField->addOptionClass('form-control');
        $input = Html::input('number', $dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTextarea(DataField $dataField) {
        $dataField->addOptionClass('form-control');
        $dataField->addOption('style','resize: none');
        $input = Html::textarea($dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getEmailInput(DataField $dataField) {
        $dataField->addOptionClass('form-control');
        $input = Html::input('email', $dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTelephoneInput(DataField $dataField) {
        $dataField->addOptionClass('form-control');
        $input = Html::input('tel', $dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getRadioVertical(DataField $dataField) {
        $input = self::renderRC('radio', $dataField->name, $dataField->value);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getRadioHorizontal(DataField $dataField) {
        $input = self::renderRC('radio', $dataField->name, $dataField->value, ['class' => 'radio-inline'], false);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getCheckVertical(DataField $dataField) {
        $input = self::renderRC('checkbox', $dataField->name, $dataField->value);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getCheckHorizontal(DataField $dataField) {
        $input = self::renderRC('checkbox', $dataField->name, $dataField->value, ['class' => 'checkbox-inline'], false);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getSelect(DataField $dataField) {
        $dataField->addOptionClass('form-control');
        $input = Html::dropDownList($dataField->name, null, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getSelectMultiple(DataField $dataField) {
        $dataField->addOption('multiple', 'true');
        $dataField->addOptionClass('form-control');
        $input = Html::dropDownList( $dataField->name, null, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTextBlock(DataField $dataField) {
        $input = Html::tag('pre', $dataField->text, $dataField->options);
        return Html::tag('div', $input, ['class' => 'col-md-12']);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getLink(DataField $dataField) {
        $dataField->addOptionClass('btn btn-link');
        return Html::a($dataField->text, null, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getButton(DataField $dataField) {
        $dataField->addOptionClass('btn btn-default btn-lg btn-block');
        return Html::button($dataField->text, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getButtonInput(DataField $dataField) {
        $dataField->addOptionClass('btn btn-default btn-lg btn-block');
        return Html::buttonInput($dataField->text, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getButtonSubmit(DataField $dataField) {
        $dataField->addOptionClass('btn btn-default btn-lg btn-block');
        return Html::submitButton($dataField->text, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getHeaderTitle(DataField $dataField) {
        return Html::tag('h3', $dataField->getParam('title'));
    }

    /**
     * Дополнительное поле подсказки.
     *
     * @param DataField $dataField
     * @return string
     */
    protected function help(DataField $dataField) {
        return Html::tag('span', $dataField->getParam('textHelp'), ['class' => 'help-block']);
    }

    /**
     * Дополнительное поле заголовка/названия поля.
     *
     * @param DataField $dataField
     * @return string
     */
    protected function label(DataField $dataField) {
        return Html::tag('label', $dataField->getParam('textLabel'), [
            'class' => 'col-md-4 control-label',
            'for' => $dataField->optionsId
        ]);
    }
}