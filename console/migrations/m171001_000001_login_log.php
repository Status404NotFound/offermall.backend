<?php

use yii\db\Migration;

class m171001_000001_login_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%login_log}}', [
            'login_log_id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->null(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('idx-login_log_datetime', 'login_log', 'datetime');
        $this->addForeignKey('login_log_fk_user_id', 'login_log', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('login_log_fk_user_id', 'login_log');
        $this->dropIndex('idx-login_log_datetime', 'login_log');
        $this->dropTable('{{%login_log}}');
    }
}
