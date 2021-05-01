<?php
namespace common\services\webmaster;

use yii\helpers\BaseArrayHelper;
use yii\helpers\ReplaceArrayValue;

class ArrayHelper extends BaseArrayHelper
{
    /**
     * @param array $data
     * @param array $map
     * @return array
     */
    public static function arrayMap(array $data, array $map): array
    {
        $res = [];
        foreach ($map as $to => $from) {
            $res[$to] = static::getValue($data, $from);
        }
        return $res;
    }
}