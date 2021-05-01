<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 08.02.17
 * Time: 12:59
 */

namespace tds\modules\genform\models\data;


interface HandlerDataInterface
{
    /**
     * Установить массив данных.
     *
     * @param array $data
     */
    public function setData(array &$data);

    /**
     * Получить массив данных.
     *
     * @return array
     */
    public function getData();
}