<?php

use yii\db\Migration;

/**
 * Handles the creation of table `product_sku`.
 */
class m100000_000001_product_sku extends Migration
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

        $this->createTable('{{%product_sku}}', [
            'sku_id' => $this->primaryKey(11),
            'product_id' => $this->integer(11)->notNull(),

            'sku_name' => $this->string(255)->notNull(),
            'sku_alias' => $this->string(255)->notNull(),

            'color' => $this->string(255)->null(),
            'img' => $this->string(255)->null(),
            'description' => $this->text()->null(),

            'active' => $this->smallInteger(1)->defaultValue(1),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('product_sku_uq1', 'product_sku', 'sku_name', true);
        $this->addForeignKey('product_sku_fk_product_id', 'product_sku', 'product_id', 'product', 'product_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('product_sku_fk_updated_by', 'product_sku', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('product_sku_fk_product_id', 'product_sku');
        $this->dropForeignKey('product_sku_fk_updated_by', 'product_sku');
        $this->dropIndex('product_sku_uq1', 'product_sku');

        $this->dropTable('{{%product_sku}}');
    }
}
