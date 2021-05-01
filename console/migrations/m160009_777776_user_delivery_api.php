<?php

use yii\db\Migration;

class m160009_777776_user_delivery_api extends Migration
{
    public function up()
    {
        $this->createTable('{{%user_delivery_api}}', [
            'api_id' => $this->primaryKey(11),
            'api_name' => $this->string(255),
            'delivery_api_id' => $this->integer(11)->notNull(),

            'credentials' => $this->string(255)->notNull(),
            'permission_api_id' => $this->integer(11)->notNull(),
            'country_id' => $this->integer(11)->notNull(),
        ]);

        $this->addForeignKey('user_delivery_api_fk_api_id', 'user_delivery_api', 'delivery_api_id', 'delivery_api', 'delivery_api_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('user_delivery_api_fk_country_id', 'user_delivery_api', 'country_id', 'countries', 'id', 'NO ACTION', 'NO ACTION');
        //$this->insertData();
    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/delivery_api/user_delivery_api.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function down()
    {
        $this->dropForeignKey('user_delivery_api_fk_api_id', 'user_delivery_api');
        $this->dropForeignKey('user_delivery_api_fk_country_id', 'user_delivery_api');
        $this->dropTable('{{%user_delivery_api}}');
    }
}
