<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 09.02.17
 * Time: 12:12
 */

namespace tds\modules\genform\models\data\field;


interface EncodeDecodeDataInterface
{
    /**
     * @return array
     */
    public function encodeDataToArray();

    /**
     * @param array $data
     * @return void
     */
    public function decodeArrayToData(array $data);
}