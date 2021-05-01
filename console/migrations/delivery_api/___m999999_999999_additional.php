<?php

use yii\db\Migration;
use yii\db\mysql\Schema;

class ___m999999_999999_additional extends Migration
{
    public function safeUp()
    {

    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/delivery_api/delivery_api.sql');
        $query .= file_get_contents(__DIR__ . '/delivery_api/credentials.sql');

        Yii::$app->db->createCommand($query)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }
}
