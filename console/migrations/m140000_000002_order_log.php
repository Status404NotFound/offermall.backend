<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_log`.
 */
class m140000_000002_order_log extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('order_log', [
            'id' => $this->primaryKey(11),
            'row_id' => $this->integer(11)->notNull(),
            'user_id' => $this->integer(11)->notNull(),

            'column' => $this->string(255)->notNull(),
            'old_data' => $this->string(255),
            'comment' => $this->string(255)->null(),
            'instrument' => $this->smallInteger(3)->notNull(),

            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        //$this->addForeignKey('order_log_fk_user_id', 'order_log', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        //$this->addForeignKey('order_log_fk_order_id', 'order_log', 'order_id', 'order', 'row_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //$this->dropForeignKey('order_log_fk_user_id', 'order_log');
        //$this->dropForeignKey('order_log_fk_order_id', 'order_log');

        $this->dropTable('order_log');
    }
}
