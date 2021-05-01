<?php

use yii\db\Migration;

/**
 * Handles the creation of table `stock_traffic`.
 */
class m140001_100002_stock_traffic extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%stock_traffic}}', [
            'transfer_id' => $this->primaryKey(11),

            'stock_id_from' => $this->integer(11)->null(),
            'stock_id_to' => $this->integer(11)->null(),

            'is_new' => $this->smallInteger(1)->defaultValue(0),
            'order_id' => $this->integer(11)->null(),

            'sku_id' => $this->integer(11)->notNull(),
            'amount' => $this->integer(11)->notNull(),

            'created_by' => $this->integer(11)->notNull(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('idx-stock_traffic_datetime', 'stock_traffic', 'datetime');

        $this->addForeignKey('stock_traffic_fk_order_id', 'stock_traffic', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('stock_traffic_fk_stock_id_from', 'stock_traffic', 'stock_id_from', 'stock', 'stock_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('stock_traffic_fk_stock_id_to', 'stock_traffic', 'stock_id_to', 'stock', 'stock_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('stock_traffic_fk_created_by', 'stock_traffic', 'created_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('stock_traffic_fk_order_id', 'stock_traffic');
        $this->dropForeignKey('stock_traffic_fk_stock_id_from', 'stock_traffic');
        $this->dropForeignKey('stock_traffic_fk_stock_id_to', 'stock_traffic');
        $this->dropForeignKey('stock_traffic_fk_created_by', 'stock_traffic');

        $this->dropIndex('idx-stock_traffic_datetime', 'stock_traffic');

        $this->dropTable('{{%stock_traffic}}');
    }
}
