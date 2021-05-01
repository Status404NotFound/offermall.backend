<?php

use yii\db\Migration;

/**
 * Handles the creation of table `flow`.
 */
class m130009_000000_flow extends Migration
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

        $this->createTable('{{%flow}}', [
            'flow_id' => $this->primaryKey(11),

            'offer_id' => $this->integer(11)->notNull(),
            'advert_offer_target_status' => $this->smallInteger(3)->notNull(),
            'wm_id' => $this->integer(11)->notNull(),

            'flow_name' => $this->string(255)->notNull(),

            'flow_key' => $this->string(255)->notNull(),
            'traffic_back' => $this->string(255)->null(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11)->notNull(),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),

            'use_tds' => $this->smallInteger(2)->defaultValue(0),
            'active' => $this->smallInteger(2)->defaultValue(1),
            'is_deleted' => $this->smallInteger(2)->defaultValue(0),
        ], $tableOptions);

        $this->addForeignKey('flow_fk_offer_id', 'flow', 'offer_id', 'offer', 'offer_id');
        $this->addForeignKey('flow_fk_wm_id', 'flow', 'wm_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('flow_fk_offer_id', 'flow');
        $this->dropForeignKey('flow_fk_wm_id', 'flow');

        $this->dropTable('{{%flow}}');
    }
}
