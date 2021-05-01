<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wm_offer`.
 */
class m160000_400007_wm_offer extends Migration
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
        $this->createTable('{{%wm_offer}}', [
            'wm_offer_id' => $this->primaryKey(11),
            'offer_id' => $this->integer(11)->notNull(),
            'wm_id' => $this->integer(11)->notNull(),
            'leads' => $this->integer(11)->notNull(),
            'status' => $this->smallInteger(3)->defaultValue(0),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);
        $this->addForeignKey('wm_offer_fk_offer_id', 'wm_offer', 'offer_id', 'offer', 'offer_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('wm_offer_fk_wm_id', 'wm_offer', 'wm_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('wm_offer_fk_offer_id', 'wm_offer');
        $this->dropForeignKey('wm_offer_fk_wm_id', 'wm_offer');
        $this->dropTable('{{%wm_offer}}');
    }
}
