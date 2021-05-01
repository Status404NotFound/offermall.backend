<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 17.01.17
 * Time: 22:07
 */

namespace tds\modules\genform\models\fields;


use common\helpers\FishHelper;
use tds\modules\genform\models\DataField;
use yii\bootstrap\Html;

class BootstrapFields extends Fields
{
    static $countField = 0;

    public function init()
    {
        $this->setOptionsWrapFields(['class' => 'form-group']);
        $this->setAfterField(Html::tag('div', null, ['class' => 'clearfix']));
    }

    /**
     * Получить обьект DataField c значениями по умолчанию которые неоходимые.
     *
     * @param int $type
     * @return DataField
     */
    public function getDefaultValue($type)
    {
        $name = str_replace(' ', '_',strtolower(Fields::getTypeName($type)))  . '-' . self::$countField++;

        $dataField = new DataField();

        $dataField->addOption('id', $name);
        $dataField->addParam('type', $type);
        $dataField->addParam('value', null);

        switch ($type):
            case Fields::TYPE_HIDDEN_INPUT:
                $dataField->addParam('name', $name);
                $dataField->addParam('value', 'hidden');
                break;
            case Fields::TYPE_TEXT_INPUT:
            case Fields::TYPE_NUMBER_INPUT:
            case Fields::TYPE_TEXTAREA:
            case Fields::TYPE_EMAIL:
            case Fields::TYPE_TELEPHONE:
                $dataField->addParam('name', $name);
                $dataField->addOption('required', false);
                $dataField->addOption('placeholder', 'Placeholder');
                $dataField->addParam('textLabel', Fields::getTypeName($type));
                $dataField->addParam('textHelp', Fields::getTypeName($type) . ' help text');
                break;
            case Fields::TYPE_RADIO_HORIZONTAL:
            case Fields::TYPE_RADIO_VERTICAL:
            case Fields::TYPE_CHECK_HORIZONTAL:
            case Fields::TYPE_CHECK_VERTICAL:
                $dataField->addParam('name', $name);
                $dataField->addOption('required', false);
                $dataField->addParam('textLabel', Fields::getTypeName($type));
                $dataField->addParam('textHelp', Fields::getTypeName($type) . ' help text');
                $dataField->addParam('value', [
                    ['text' => 'test1', 'options'=> ['id' => $name, 'value' => '', ]],
                    ['text' => 'test2', 'options'=> ['id' => $name, 'value' => '', ]],
                    ['text' => 'test3', 'options'=> ['id' => $name, 'value' => '', ]],
                ]);
                break;
            case Fields::TYPE_SELECT:
            case Fields::TYPE_SELECT_MULTIPLE:
                $dataField->addParam('name', $name);
                $dataField->addOption('required', false);
                $dataField->addParam('textLabel', Fields::getTypeName($type));
                $dataField->addParam('textHelp', Fields::getTypeName($type) . ' help text');
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
     * Получить
     *
     * @return string
     */
    public function getJS()
    {
        // TODO: Implement getJS() method.
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
/*
label {
    text-align: right;
}
.form-group {
    position: relative;
    margin-bottom: 7px;
    user-select: none;
    padding: 5px 0 10px;
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
*/
            
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
//        $dataField->addCssClass('form-control');
        $input = Html::input('text', $dataField->name, $dataField->value, $dataField->options );
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getNumberInput(DataField $dataField) {
//        $dataField->addCssClass('form-control');
        $input = Html::input('number', $dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTextarea(DataField $dataField) {
//        $dataField->addCssClass('form-control');
        $dataField->addOption('style','resize: none');
        $input = Html::textarea($dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getEmailInput(DataField $dataField) {
//        $dataField->addCssClass('form-control');
        $input = Html::input('email', $dataField->name, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTelephoneInput(DataField $dataField) {
//        $dataField->addCssClass('form-control');
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
//        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), ['class' => 'col-md-8']);
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
//        $dataField->addCssClass('form-control');
        $input = Html::dropDownList($dataField->name, null, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getSelectMultiple(DataField $dataField) {
        $dataField->addOption('multiple', 'true');
//        $dataField->addCssClass('form-control');
        $input = Html::dropDownList( $dataField->name, null, $dataField->value, $dataField->options);
        return $this->label($dataField) . Html::tag('div', $input . $this->help($dataField), []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getTextBlock(DataField $dataField) {
        $input = Html::tag('pre', $dataField->text, $dataField->options);
        return Html::tag('div', $input, []);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getLink(DataField $dataField) {
        return Html::a($dataField->text, null, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getButton(DataField $dataField) {
        return Html::button($dataField->text, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getButtonInput(DataField $dataField) {
        return Html::buttonInput($dataField->text, $dataField->options);
    }

    /**
     * @param DataField $dataField
     * @return string
     */
    protected function getButtonSubmit(DataField $dataField) {
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
        return Html::tag('span', $dataField->getParam('textHelp'), []);
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