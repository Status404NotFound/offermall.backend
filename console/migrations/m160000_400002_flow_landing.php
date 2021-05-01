<?php

use yii\db\Migration;

/**
 * Handles the creation of table `flow`.
 */
class m160000_400002_flow_landing extends Migration
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

        $this->createTable('{{%flow_landing}}', [
            'id' => $this->primaryKey(11),
            'flow_id' => $this->integer(11)->notNull(),
            'landing_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->addForeignKey('flow_landing_fk_flow_id', 'flow_landing', 'flow_id', 'flow', 'flow_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('flow_landing_fk_landing_id', 'flow_landing', 'landing_id', 'landing', 'landing_id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('flow_landing_fk_flow_id', 'flow_landing');
        $this->dropForeignKey('flow_landing_fk_landing_id', 'flow_landing');

        $this->dropTable('{{%flow_landing}}');
    }
}
