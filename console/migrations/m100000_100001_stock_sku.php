<?php

use yii\db\Migration;

/**
 * Handles the creation of table `stock_sku`.
 */
class m100000_100001_stock_sku extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%stock_sku}}', [
            'stock_sku_id' => $this->primaryKey(11),

            'stock_id' => $this->integer(11)->notNull(),
            'sku_id' => $this->integer(11)->notNull(),

            'amount' => $this->integer(11)->null(),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('stock_sku_uq1', 'stock_sku', ['stock_id', 'sku_id'], true);
        $this->addForeignKey('stock_sku_fk_stock_id', 'stock_sku', 'stock_id', 'stock', 'stock_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('stock_sku_fk_sku_id', 'stock_sku', 'sku_id', 'product_sku', 'sku_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('stock_sku_fk_updated_by', 'stock_sku', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('stock_sku_fk_stock_id', 'stock_sku');
        $this->dropForeignKey('stock_sku_fk_sku_id', 'stock_sku');
        $this->dropForeignKey('stock_sku_fk_updated_by', 'stock_sku');
        $this->dropIndex('stock_sku_uq1', 'stock_sku');

        $this->dropTable('{{%stock_sku}}');
    }
}
