<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 20.02.17
 * Time: 21:29
 */

namespace tds\modules\genform\models\builder\wrap;


use tds\modules\genform\models\data\field\DataField;
use yii\bootstrap\Html;

trait Wrap
{
    private $optionsWrap = array();
    private $beforeField = array();
    private $afterField = array();

    /**
     * Добавить массив опций для обьертки поля.
     *
     * @param array $optionsWrapFields
     * @param string $group
     * @throws \Error
     * @internal param array $optionsWrap
     */
    public function setOptionsWrapFields(array $optionsWrapFields, $group)
    {
        $optionsWrap = &$this->optionsWrap[$group];

        foreach ($optionsWrapFields as $key => $option) {
            if (isset($optionsWrap[$key]) && $key === 'class') {
                Html::addCssClass($optionsWrap, $option);
            } elseif (is_scalar($option) || is_callable($option)) {
                $optionsWrap[$key] = $option;
            } else {
                throw new \Error('The value must be a scalar');
            }
        }
    }

    /**
     * Получить массив опций для обьертки поля.
     *
     * @param DataField $dataField
     * @param string $group
     * @return array
     */
    public function getOptionsWrapFields(DataField $dataField, $group)
    {
        $array = [];

        if ( isset($this->optionsWrap[$group]) ) {
            $optionsWrap = $this->optionsWrap[$group];

            foreach ($optionsWrap as $key => $item) {
                if (is_callable($item)) {
                    $array[$key] = call_user_func($item, $dataField, $group);
                } else {
                    $array[$key] = $item;
                }
            }
        }

        return $array;
    }

    /**
     * Вставить строку после поля.
     *
     * @param string $afterField
     * @param string $group
     * @throws \Error
     */
    public function setAfterField($afterField, $group)
    {
        if (is_string($afterField) || is_callable($afterField)) {
            $this->afterField[$group][] = $afterField;
        } else {
            throw new \Error('The value must be a string or callable');
        }
    }

    /**
     * Получить строку для вставки после поля.
     *
     * @param DataField $dataField
     * @param string $group
     * @return string
     */
    public function getAfterField(DataField $dataField, $group)
    {
        $html = '';

        if ( isset($this->afterField[$group]) ) {
            foreach ($this->afterField[$group] as $item) {
                if (is_string($item)) {
                    $html .= $item;
                } elseif (is_callable($item)) {
                    $html .= call_user_func($item, $dataField);
                }
            }
        }

        return $html;
    }

    /**
     * Вставить строку перед полем.
     *
     * @param string $beforeField
     * @param string $group
     * @throws \Error
     */
    public function setBeforeField($beforeField, $group) {
        if (is_string($beforeField) || is_callable($beforeField)) {
            $this->beforeField[$group][] = $beforeField;
        } else {
            throw new \Error('The value must be a string or callable');
        }
    }

    /**
     * Получить строку для вставки перед полем.
     *
     * @param DataField $dataField
     * @param string $group
     * @return string
     */
    public function getBeforeField(DataField $dataField, $group) {
        $html = '';

        if ( isset($this->beforeField[$group]) ) {
            foreach ($this->beforeField[$group] as $item) {
                if (is_string($item)) {
                    $html .= $item;
                } elseif (is_callable($item)) {
                    $html .= call_user_func($item, $dataField);
                }
            }
        }

        return $html;
    }

    /**
     * Метод реализующий создание обертки поля.
     *
     * @param $field
     * @param DataField $dataField
     * @param string $group
     * @return string
     * @internal param ThemeFieldsInterface $themeFields
     * @internal param $field
     */
    public function wrapFields($field, DataField $dataField, $group)
    {

        $field = $this->getBeforeField($dataField, $group) . $field . $this->getAfterField($dataField, $group);

        if (empty($this->getOptionsWrapFields($dataField, $group))) {
            return $field;
        }

        return Html::tag('div',$field, $this->getOptionsWrapFields($dataField, $group));
    }
}