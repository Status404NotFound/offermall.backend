<?php

use yii\db\Migration;

class m999999_999999_additional extends Migration
{
    public function safeUp()
    {
        $query = file_get_contents(__DIR__ . '/sql/cur_and_money.sql');
        Yii::$app->db->createCommand($query)->execute();
//        $this->insertData();
    }

//    public function insertData()
//    {
//        $query = file_get_contents(__DIR__ . 'sql/cur_and_money.sql');
//        Yii::$app->db->createCommand($query)->execute();
//    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }
}