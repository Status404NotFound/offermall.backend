<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_wm_group`.
 */
class m120000_200001_target_wm_group extends Migration
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

        $this->createTable('{{%target_wm_group}}', [
            'target_wm_group_id' => $this->primaryKey(11),
            'wm_offer_target_id' => $this->integer(11)->notNull(),

            'base_commission' => $this->double()->null(),
            'exceeded_commission' => $this->double()->null(),
            'use_commission_rules' => $this->smallInteger(1)->defaultValue(0),

            'hold' => $this->smallInteger(3)->defaultValue(0),

            'active' => $this->smallInteger(2)->defaultValue(0),
            'view_for_all' => $this->smallInteger(2)->defaultValue(1),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->addForeignKey('target_wm_group_fk_wm_offer_target_id', 'target_wm_group', 'wm_offer_target_id', 'wm_offer_target', 'wm_offer_target_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_wm_group_fk_updated_by', 'target_wm_group', 'updated_by', 'user', 'id', 'NO ACTION', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_wm_group_fk_wm_offer_target_id', 'target_wm_group');
        $this->dropForeignKey('target_wm_group_fk_updated_by', 'target_wm_group');

        $this->dropTable('{{%target_wm_group}}');
    }
}
