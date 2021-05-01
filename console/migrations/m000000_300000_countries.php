<?php

use yii\db\Migration;
use yii\db\Schema;

class m000000_300000_countries extends Migration
{
    public function up()
    {
        $query = file_get_contents(__DIR__ . '/sql/countries.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        $this->dropTable('countries');
    }
}
