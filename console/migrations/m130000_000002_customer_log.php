<?php

use yii\db\Migration;

/**
 * Handles the creation of table `customer_log`.
 */
class m130000_000002_customer_log extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('customer_log', [
            'id' => $this->primaryKey(11),
            'row_id' => $this->integer(11)->notNull(),
            'user_id' => $this->integer(11)->notNull(),

            'column' => $this->string(255)->notNull(),
            'old_data' => $this->string(255),
            'comment' => $this->string(255)->null(),
            'instrument' => $this->smallInteger(3)->notNull(),

            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        //$this->addForeignKey('customer_log_fk_user_id', 'customer_log', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        //$this->addForeignKey('customer_log_fk_customer_id', 'customer_log', 'row_id', 'customer', 'customer_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //$this->dropForeignKey('customer_log_fk_user_id', 'customer_log');
        //$this->dropForeignKey('customer_log_fk_customer_id', 'customer_log');

        $this->dropTable('customer_log');
    }
}
