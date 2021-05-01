<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_advert_sku`.
 */
class m120000_300000_target_advert_sku extends Migration
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

        $this->createTable('{{%target_advert_sku}}', [
            'target_advert_sku_id' => $this->primaryKey(11),

            'target_advert_id' => $this->integer(11)->notNull(),
            'sku_id' => $this->integer(11)->null(),

            'base_cost' => $this->double()->null(),
            'exceeded_cost' => $this->double()->null(),

            'is_upsale' => $this->smallInteger(1)->defaultValue(0),
            'is_bookkeeping' => $this->smallInteger(1)->defaultValue(1),

            'use_sku_cost_rules' => $this->smallInteger(1)->defaultValue(0),
            'use_extended_rules' => $this->smallInteger(1)->defaultValue(0),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->addForeignKey('target_advert_sku_fk_target_advert_id', 'target_advert_sku', 'target_advert_id', 'target_advert', 'target_advert_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_sku_fk_sku_id', 'target_advert_sku', 'sku_id', 'product_sku', 'sku_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_sku_fk_updated_by', 'target_advert_sku', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_advert_sku_fk_target_advert_id', 'target_advert_sku');
        $this->dropForeignKey('target_advert_sku_fk_sku_id', 'target_advert_sku');
        $this->dropForeignKey('target_advert_sku_fk_updated_by', 'target_advert_sku');

        $this->dropTable('{{%target_advert_sku}}');
    }
}
