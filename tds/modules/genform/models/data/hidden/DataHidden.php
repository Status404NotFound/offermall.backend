<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 07.02.17
 * Time: 1:31
 */

namespace tds\modules\genform\models\data\hidden;


use common\helpers\FishHelper;
use tds\modules\genform\models\data\HandlerDataInterface;
use tds\modules\genform\models\builder\theme\Theme;
use tds\modules\genform\models\builder\theme\ThemeInterface;

class DataHidden implements HandlerDataInterface
{
    private $data = [];

    public function addField(ThemeInterface $themeFields, $name, $value = '', $options = []) {
        $dataFields = $themeFields->getDefaultValue(Theme::TYPE_HIDDEN_INPUT);

        $dataFields->name = $name;
        $dataFields->value = $value;
        $dataFields->options = $options;
        array_push($this->data, $dataFields);

        return $dataFields;
    }

    public function getFields() {
        return $this->data;
    }

    /**
     * Установить массив данных.
     *
     * @param array $data
     */
    public function setData(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * Получить массив данных.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}