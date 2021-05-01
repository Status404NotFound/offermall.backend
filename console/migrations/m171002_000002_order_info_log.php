<?php

use yii\db\Migration;

class m171002_000002_order_info_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%order_info_log}}', [
            'order_info_log_id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->notNull(),
            'order_id' => $this->integer(11)->notNull(),
            'customer_id' => $this->integer(11)->notNull(),
            'order_data_id' => $this->integer(11)->notNull(),

            'model' => $this->string(255)->notNull(),
            'field_name' => $this->string(255)->notNull(),
            'last_value' => $this->text()->null(),
            'new_value' => $this->text()->null(),

            'instrument' => $this->smallInteger(3)->notNull(),
            'ip' => $this->string(48)->notNull(),
            'city' => $this->string(255)->null(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'comment' => $this->text()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-order_info_log_datetime', 'order_info_log', 'datetime');
        $this->addForeignKey('order_info_log_fk_user_id', 'order_info_log', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_info_log_fk_order_id', 'order_info_log', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_info_log_fk_customer_id', 'order_info_log', 'customer_id', 'customer', 'customer_id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('order_info_log_fk_user_id', 'order_info_log');
        $this->dropForeignKey('order_info_log_fk_order_id', 'order_info_log');
        $this->dropForeignKey('order_info_log_fk_customer_id', 'order_info_log');
        $this->dropIndex('idx-order_info_log_datetime', 'order_info_log');
        $this->dropTable('{{%order_info_log}}');
    }
}
