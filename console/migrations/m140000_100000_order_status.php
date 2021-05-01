<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_status`.
 */
class m140000_100000_order_status extends Migration
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
        $this->createTable('order_status', [
            'id' => $this->primaryKey(),
            'status_id' => $this->smallInteger(3)->notNull(),
            'status_name' => $this->string(255)->notNull(),
            'is_valid' => $this->smallInteger(3)->notNull()->defaultValue(1),
            'has_reason' => $this->smallInteger(3)->notNull()->defaultValue(0),
        ], $tableOptions);
        $this->createIndex('idx-order_status_id', 'order_status', 'status_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-order_status_id', 'order_status');
        $this->dropTable('order_status');
    }
}
