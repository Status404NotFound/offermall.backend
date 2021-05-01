<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 15.01.17
 * Time: 20:47
 */

namespace tds\modules\genform\models\fields;


use common\helpers\FishHelper;
use tds\modules\genform\models\DataField;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

abstract class Fields extends Object implements FieldsInterface, FieldsWrapInterface
{

    /**
     * Константы типов полей
     */
    const TYPE_HIDDEN_INPUT = 0;
    const TYPE_TEXT_INPUT = 1;
    const TYPE_NUMBER_INPUT = 2;
    const TYPE_TEXTAREA = 3;
    const TYPE_EMAIL = 4;
    const TYPE_TELEPHONE = 5;

    const TYPE_RADIO_VERTICAL = 6;
    const TYPE_RADIO_HORIZONTAL = 7;
    const TYPE_CHECK_VERTICAL = 8;
    const TYPE_CHECK_HORIZONTAL = 9;

    const TYPE_SELECT = 10;
    const TYPE_SELECT_MULTIPLE = 11;

    const TYPE_TEXT_BLOCK = 12;
    const TYPE_LINK = 13;
    const TYPE_BUTTON = 14;
    const TYPE_BUTTON_INPUT = 15;
    const TYPE_BUTTON_SUBMIT = 16;

    const TYPE_HEADER_TITLE = 17;

    private $optionsWrap = [];
    private $beforeField = [];
    private $afterField = [];


    /**
     * Получить название типа "Title" по константе типа или массив всех "Title" значений.
     * Если имя не найдено будет сгенерированна ошибка.
     *
     * @param null $type
     * @return array|mixed
     * @throws \Error
     */
    public static function getTypeName($type = null) {
        if (isset($type)) {
            $arr = self::getTypeName();
            if (isset($arr[$type])) {
                return $arr[$type];
            }
            throw new \Error('Class name (' . $type . ') is not in the list');
        }
        return [
            self::TYPE_HIDDEN_INPUT => 'Hidden',
            self::TYPE_TEXT_INPUT => 'Text input',
            self::TYPE_NUMBER_INPUT => 'Number input',
            self::TYPE_TEXTAREA => 'Textarea',
            self::TYPE_EMAIL => 'Email input',
            self::TYPE_TELEPHONE => 'Telephone input',

            self::TYPE_RADIO_VERTICAL => 'Radio vertical',
            self::TYPE_RADIO_HORIZONTAL => 'Radio horizontal',
            self::TYPE_CHECK_VERTICAL => 'Check vertical',
            self::TYPE_CHECK_HORIZONTAL => 'Check horizontal',

            self::TYPE_SELECT => 'Select',
            self::TYPE_SELECT_MULTIPLE => 'Select multiple',

            self::TYPE_TEXT_BLOCK => 'Text block',
            self::TYPE_LINK => 'Link button',
            self::TYPE_BUTTON => 'Button',
            self::TYPE_BUTTON_INPUT => 'Button input',
            self::TYPE_BUTTON_SUBMIT => 'Button submit',

            self::TYPE_HEADER_TITLE => 'Header title',
        ];
    }

    /**
     * Получить HTML код поля указаного типа которое было создано на основе DataField значений по умолчанию.
     *
     * @param string $type
     * @return string HTML
     */
    public function getDefaultHTML($type) {
        return static::getHTML(static::getDefaultValue($type));
    }

    /**
     * Получить HTML код поля которое было создано на основе DataField значений.
     *
     * @param DataField $dataField
     * @return mixed
     * @throws \Error
     */
    public function getHTML(DataField $dataField) {
        switch ($dataField->type) :
            case self::TYPE_HIDDEN_INPUT:
                $field = static::getHiddenInput($dataField);
                break;
            case self::TYPE_TEXT_INPUT:
                $field = static::getTextInput($dataField);
                break;
            case self::TYPE_NUMBER_INPUT:
                $field = static::getNumberInput($dataField);
                break;
            case self::TYPE_TEXTAREA:
                $field = static::getTextarea($dataField);
                break;
            case self::TYPE_EMAIL:
                $field = static::getEmailInput($dataField);
                break;
            case self::TYPE_TELEPHONE:
                $field = static::getTelephoneInput($dataField);
                break;
            case self::TYPE_RADIO_VERTICAL:
                $field = static::getRadioVertical($dataField);
                break;
            case self::TYPE_RADIO_HORIZONTAL:
                $field = static::getRadioHorizontal($dataField);
                break;
            case self::TYPE_CHECK_VERTICAL:
                $field = static::getCheckVertical($dataField);
                break;
            case self::TYPE_CHECK_HORIZONTAL:
                $field = static::getCheckHorizontal($dataField);
                break;
            case self::TYPE_SELECT:
                $field = static::getSelect($dataField);
                break;
            case self::TYPE_SELECT_MULTIPLE:
                $field = static::getSelectMultiple($dataField);
                break;
            case self::TYPE_TEXT_BLOCK:
                $field = static::getTextBlock($dataField);
                break;
            case self::TYPE_LINK:
                $field = static::getLink($dataField);
                break;
            case self::TYPE_BUTTON:
                $field = static::getButton($dataField);
                break;
            case self::TYPE_BUTTON_INPUT:
                $field = static::getButtonInput($dataField);
                break;
            case self::TYPE_BUTTON_SUBMIT:
                $field = static::getButtonSubmit($dataField);
                break;
            case self::TYPE_HEADER_TITLE:
                $field = static::getHeaderTitle($dataField);
                break;
            default: throw new \Error('Unknown type of field - ' . $dataField->type);
        endswitch;

        return $this->wrapFields($field, $dataField);
    }


    abstract protected function getHiddenInput(DataField $dataField);
    abstract protected function getTextInput(DataField $dataField);
    abstract protected function getNumberInput(DataField $dataField);
    abstract protected function getTextarea(DataField $dataField);
    abstract protected function getEmailInput(DataField $dataField);
    abstract protected function getTelephoneInput(DataField $dataField);
    abstract protected function getRadioVertical(DataField $dataField);
    abstract protected function getRadioHorizontal(DataField $dataField);
    abstract protected function getCheckVertical(DataField $dataField);
    abstract protected function getCheckHorizontal(DataField $dataField);
    abstract protected function getSelect(DataField $dataField);
    abstract protected function getSelectMultiple(DataField $dataField);
    abstract protected function getTextBlock(DataField $dataField);
    abstract protected function getLink(DataField $dataField);
    abstract protected function getButton(DataField $dataField);
    abstract protected function getButtonInput(DataField $dataField);
    abstract protected function getButtonSubmit(DataField $dataField);
    abstract protected function getHeaderTitle(DataField $dataField);


    /**
     * Получить массив опций для обьертки поля.
     *
     * @param DataField $dataField
     * @return array
     */
    public function getOptionsWrapFields(DataField $dataField = null)
    {
        if (is_null($dataField)) {
            return $this->optionsWrap;
        }
        $array = [];
        foreach ($this->optionsWrap as $key => $item) {
            if (is_callable($item)) {
                $array[$key] = call_user_func($item,$dataField);
            } else {
                $array[$key] = $item;
            }
        }

        return $array;
    }

    /**
     * Добавить массив опций для обьертки поля.
     *
     * @param array $optionsWrap
     */
    public function setOptionsWrapFields(array $optionsWrap)
    {
        foreach ($optionsWrap as $key => $option) {
            if (isset($this->optionsWrap[$key]) && $key === 'class' && is_string($option)) {
                Html::addCssClass($this->optionsWrap, $option);
            } else {
                $this->optionsWrap[$key] = $option;
            }
        }
    }

    /**
     * Вставить строку после поля.
     *
     * @param string $afterField
     * @throws \Error
     */
    public function setAfterField($afterField)
    {
        if (is_string($afterField) || is_callable($afterField)) {
            $this->afterField[] = $afterField;
        } else {
            throw new \Error('The value must be a string or callable');
        }
    }

    /**
     * Вставить строку перед полем.
     *
     * @param string $beforeField
     * @throws \Error
     */
    public function setBeforeField($beforeField)
    {
        if (is_string($beforeField) || is_callable($beforeField)) {
            $this->beforeField[] = $beforeField;
        } else {
            throw new \Error('The value must be a string or callable');
        }
    }

    /**
     * Получить строку для вставки перед полем.
     *
     * @param DataField $dataField
     * @return string
     */
    private function getBeforeField(DataField $dataField)
    {
        $html = '';
        foreach ($this->beforeField as $item) {
            if (is_string($item)) {
                $html .= $item;
            } elseif (is_callable($item)) {
                $html .= call_user_func($item,$dataField);
            }
        }
        return $html;
    }

    /**
     * Получить строку для вставки после поля.
     *
     * @param DataField $dataField
     * @return string
     */
    private function getAfterField(DataField $dataField)
    {
        $html = '';
        foreach ($this->afterField as $item) {
            if (is_string($item)) {
                $html .= $item;
            } elseif (is_callable($item)) {
                $html .= call_user_func($item,$dataField);
            }
        }
        return $html;
    }

    /**
     * Метод реализующий создание обертки поля.
     *
     * @param $field
     * @param DataField $dataField
     * @return string
     */
    private function wrapFields($field, DataField $dataField)
    {
        $field = $this->getBeforeField($dataField) . $field . $this->getAfterField($dataField);

        if (empty($this->optionsWrapFields)) {
            return $field;
        }
        return Html::tag('div',$field, $this->getOptionsWrapFields($dataField));
    }

}