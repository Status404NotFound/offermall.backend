<?php

use yii\db\Migration;

class m150000_000001_call_list_log extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('call_list_log', [
            'id' => $this->primaryKey(11),

            'row_id' => $this->integer(11)->notNull(),
            'user_id' => $this->integer(11)->notNull(),

            'column' => $this->string(255)->notNull(),
            'old_data' => $this->string(255),
            'comment' => $this->string(255)->null(),
            'instrument' => $this->smallInteger(3)->notNull(),

            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);
    }


    public function safeDown()
    {
        $this->dropTable('call_list_log');
    }
}
