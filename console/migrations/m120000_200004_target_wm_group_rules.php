<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_wm_group_rules`.
 */
class m120000_200004_target_wm_group_rules extends Migration
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

        $this->createTable('{{%target_wm_group_rules}}', [
            'rule_id' => $this->primaryKey(11),

            'target_wm_group_id' => $this->integer(11)->notNull(),

            'amount' => $this->integer(11)->notNull(),
            'commission' => $this->double()->notNull(),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('target_wm_group_rules_uq1', 'target_wm_group_rules', ['target_wm_group_id', 'amount'], true);

        $this->addForeignKey('target_wm_group_rules_fk_target_wm_group_id', 'target_wm_group_rules', 'target_wm_group_id', 'target_wm_group', 'target_wm_group_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_wm_group_rules_fk_updated_by', 'target_wm_group_rules', 'updated_by', 'user', 'id', 'NO ACTION', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_wm_group_rules_fk_target_wm_group_id', 'target_wm_group_rules');
        $this->dropForeignKey('target_wm_group_rules_fk_updated_by', 'target_wm_group_rules');

        $this->dropIndex('target_wm_group_rules_uq1', 'target_wm_group_rules');

        $this->dropTable('{{%target_wm_group_rules}}');
    }
}
