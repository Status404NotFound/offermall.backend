<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_wm`.
 */
class m120000_200002_target_wm extends Migration
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

        $this->createTable('{{%target_wm}}', [
            'target_wm_id' => $this->primaryKey(11),
            'target_wm_group_id' => $this->integer(11)->notNull(),
            'wm_id' => $this->integer(11)->null(),
            'excepted' => $this->smallInteger(1)->defaultValue(0),
            'active' => $this->smallInteger(1)->notNull()->defaultValue(1),
        ], $tableOptions);

        $this->createIndex('target_wm_uq1', 'target_wm', ['target_wm_group_id', 'wm_id'], true);

        $this->addForeignKey('target_wm_fk_target_wm_group_id', 'target_wm', 'target_wm_group_id', 'target_wm_group', 'target_wm_group_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_wm_fk_wm_id', 'target_wm', 'wm_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_wm_fk_target_wm_group_id', 'target_wm');
        $this->dropForeignKey('target_wm_fk_wm_id', 'target_wm');
        $this->dropIndex('target_wm_uq1', 'target_wm');

        $this->dropTable('{{%target_wm}}');
    }
}
