<?php

use yii\db\Migration;

class m160009_777775_delivery_api extends Migration
{
    public function up()
    {
        $this->createTable('{{%delivery_api}}', [
            'delivery_api_id' => $this->primaryKey(11),

            'api_name' => $this->string(255)->notNull(),
            'api_alias' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
        ]);
        $this->insertData();
    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/delivery_api/delivery_api.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function down()
    {
        $this->dropTable('{{%delivery_api}}');
    }
}
