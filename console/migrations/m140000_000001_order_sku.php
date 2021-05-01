<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_sku`.
 */
class m140000_000001_order_sku extends Migration
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

        $this->createTable('{{%order_sku}}', [
            'order_sku_id' => $this->primaryKey(11),
            'order_id' => $this->integer(11)->notNull(),
            'sku_id' => $this->integer(11)->notNull(),

            'amount' => $this->smallInteger(4)->defaultValue(0),
            'cost' => $this->double()->null(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('order_sku_uq1', 'order_sku', ['order_id', 'sku_id'], true);

        $this->addForeignKey('order_sku_fk_created_by', 'order_sku', 'created_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_sku_fk_updated_by', 'order_sku', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_sku_fk_order_id', 'order_sku', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_sku_fk_sku_id', 'order_sku', 'sku_id', 'product_sku', 'sku_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('order_sku_fk_order_id', 'order_sku');
        $this->dropForeignKey('order_sku_fk_sku_id', 'order_sku');
        $this->dropForeignKey('order_sku_fk_created_by', 'order_sku');
        $this->dropForeignKey('order_sku_fk_updated_by', 'order_sku');
        $this->dropIndex('order_sku_uq1', 'order_sku');

        $this->dropTable('{{%order_sku}}');
    }
}
