<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 08.02.17
 * Time: 10:48
 */

namespace tds\modules\genform\models\data\extension;


use tds\modules\genform\models\data\HandlerDataInterface;

class DataExtension implements HandlerDataInterface
{
    private $data = [];

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