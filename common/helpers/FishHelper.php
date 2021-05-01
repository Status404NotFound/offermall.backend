<?php

namespace common\helpers;

use Yii;
use yii\db\ActiveQuery;

class FishHelper
{
    /**
     * @param $value
     * @param bool $is_die
     * @param bool $is_var_dump
     * @throws \yii\base\ExitException
     */
    static public function debug($value, $is_var_dump = false, $is_die = true)
    {
        echo "<pre>";
        if ($is_var_dump) {
            var_dump($value);
        } else {
            print_r($value);
        }
        echo "</pre>";
        if ($is_die) Yii::$app->end();
    }

    /**
     * @param $value
     * @param bool $is_die
     * @param bool $is_var_dump
     * @throws \yii\base\ExitException
     */
    static public function dd($value, $is_var_dump = false, $is_die = true)
    {
        if ($is_var_dump) {
            var_dump($value);
        } else {
            print_r($value);
        }
        if ($is_die) Yii::$app->end();
    }

    /**
     * @param $query
     * @param bool $is_die
     * @throws \yii\base\ExitException
     */
    public static function rawSql($query, $is_die = true)
    {
        /* @var $query ActiveQuery */
        var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);

        if ($is_die) Yii::$app->end();
    }
}
