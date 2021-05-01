<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_advert_sku_rules`.
 */
class m120000_300001_target_advert_sku_rules extends Migration
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

        $this->createTable('{{%target_advert_sku_rules}}', [
            'target_advert_sku_rule_id' => $this->primaryKey(11),

            'target_advert_id' => $this->integer(11)->notNull(),
            'target_advert_sku_id' => $this->integer(11)->notNull(),
            'sku_id' => $this->integer(11)->null(),

            'amount' => $this->integer(11)->notNull(),
            'cost' => $this->double()->notNull(),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('target_advert_sku_rules_uq1', 'target_advert_sku_rules', ['target_advert_id', 'sku_id', 'amount'], true);

        $this->addForeignKey('target_advert_sku_rules_fk_target_advert_id', 'target_advert_sku_rules', 'target_advert_id', 'target_advert', 'target_advert_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_sku_rules_fk_target_advert_sku_id', 'target_advert_sku_rules', 'target_advert_sku_id', 'target_advert_sku', 'target_advert_sku_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_sku_rules_fk_sku_id', 'target_advert_sku_rules', 'sku_id', 'product_sku', 'sku_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_sku_rules_fk_updated_by', 'target_advert_sku_rules', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_advert_sku_rules_fk_target_advert_id', 'target_advert_sku_rules');
        $this->dropForeignKey('target_advert_sku_rules_fk_target_advert_sku_id', 'target_advert_sku_rules');
        $this->dropForeignKey('target_advert_sku_rules_fk_sku_id', 'target_advert_sku_rules');
        $this->dropForeignKey('target_advert_sku_rules_fk_updated_by', 'target_advert_sku_rules');

        $this->dropIndex('target_advert_sku_rules_uq1', 'target_advert_sku_rules');

        $this->dropTable('{{%target_advert_sku_rules}}');
    }
}
