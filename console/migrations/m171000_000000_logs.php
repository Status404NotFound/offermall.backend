<?php

use yii\db\Migration;

class m171000_000000_logs extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%logs}}', [
            'id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->notNull(),
            'ip' => $this->string(255)->notNull(),
            'model' => $this->string(255)->notNull(),
            'last_data' => $this->text()->null(),
            'new_data' => $this->text()->notNull(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'comment' => 'LONGTEXT NOT NULL',
        ], $tableOptions);

        $this->createIndex('idx-logs_log_datetime', 'logs', 'datetime');
        $this->addForeignKey('logs_fk_user_id', 'logs', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('logs_fk_user_id', 'logs');
        $this->dropIndex('idx-logs_log_datetime', 'logs');
        $this->dropTable('{{%logs}}');
    }
}
