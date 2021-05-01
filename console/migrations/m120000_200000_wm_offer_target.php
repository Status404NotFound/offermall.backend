<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wm_offer_target`.
 */
class m120000_200000_wm_offer_target extends Migration
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

        $this->createTable('{{%wm_offer_target}}', [
            'wm_offer_target_id' => $this->primaryKey(11),

            'offer_id' => $this->integer(11)->notNull(),
            'advert_offer_target_status' => $this->smallInteger(3)->notNull(),
            'wm_offer_target_status' => $this->integer(11)->null(),
            'geo_id' => $this->integer(11)->null(),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),

            'active' => $this->smallInteger(1)->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('idx-wm_offer_target_status', 'wm_offer_target', 'wm_offer_target_status');
        $this->createIndex('wm_offer_target_uq1', 'wm_offer_target', ['advert_offer_target_status', 'wm_offer_target_status', 'geo_id', 'offer_id'], true);

        $this->addForeignKey('wm_offer_target_fk_offer_id', 'wm_offer_target', 'offer_id', 'offer', 'offer_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('wm_offer_target_fk_geo_id', 'wm_offer_target', 'geo_id', 'geo', 'geo_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('wm_offer_target_fk_updated_by', 'wm_offer_target', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('wm_offer_target_fk_offer_id', 'wm_offer_target');
        $this->dropForeignKey('wm_offer_target_fk_geo_id', 'wm_offer_target');
        $this->dropForeignKey('wm_offer_target_fk_updated_by', 'wm_offer_target');

        $this->dropIndex('wm_offer_target_uq1', 'wm_offer_target');
        $this->dropIndex('idx-wm_offer_target_status', 'wm_offer_target');

        $this->dropTable('{{%wm_offer_target}}');
    }
}
