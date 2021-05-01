<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_sku_log`.
 */
class m140000_000003_order_sku_log extends Migration
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

        $this->createTable('order_sku_log', [
            'id' => $this->primaryKey(11),

            'order_sku_id' => $this->integer(11)->notNull(),
            'order_id' => $this->integer(11)->notNull(),
            'sku_id' => $this->integer(11)->notNull(),

            'last_amount' => $this->smallInteger(4)->null(),
            'new_amount' => $this->smallInteger(4)->null(),

//            'last_cost' => $this->double()->null(),
//            'new_cost' => $this->double()->null(),

            'instrument' => $this->smallInteger(3)->notNull(),
            'delete' => $this->smallInteger(1)->notNull()->defaultValue(0),

            'user_id' => $this->integer(11)->notNull(),
            'ip' => $this->string(48)->notNull(),
            'city' => $this->string(255)->null(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'comment' => $this->string(255)->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-order_sku_log', 'order_sku_log', ['order_id', 'sku_id'], false);
        $this->addForeignKey('order_sku_log_fk_user_id', 'order_sku_log', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_sku_log_fk_order_id', 'order_sku_log', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_sku_log_fk_sku_id', 'order_sku_log', 'sku_id', 'product_sku', 'sku_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('order_sku_log_fk_user_id', 'order_sku_log');
        $this->dropForeignKey('order_sku_log_fk_order_id', 'order_sku_log');
        $this->dropForeignKey('order_sku_log_fk_sku_id', 'order_sku_log');
        $this->dropIndex('idx-order_sku_log', 'order_sku_log');

        $this->dropTable('order_sku_log');
    }
}
