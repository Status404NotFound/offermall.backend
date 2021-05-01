<?php

use yii\db\Migration;

/**
 * Handles the creation of table `advert_offer_target`.
 */
class m120000_100000_advert_offer_target extends Migration
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

        $this->createTable('{{%advert_offer_target}}', [
            'advert_offer_target_id' => $this->primaryKey(11),

            'offer_id' => $this->integer(11)->notNull(),
            'advert_offer_target_status' => $this->smallInteger(3)->notNull(),
            'geo_id' => $this->integer(11)->notNull(),

            'wm_visible' => $this->smallInteger(1)->defaultValue(1),
            'active' => $this->smallInteger(1)->defaultValue(0),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('advert_offer_target_uq1', 'advert_offer_target', ['advert_offer_target_status', 'geo_id', 'offer_id'], true);

        $this->addForeignKey('advert_offer_target_fk_offer_id', 'advert_offer_target', 'offer_id', 'offer', 'offer_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('advert_offer_target_fk_geo_id', 'advert_offer_target', 'geo_id', 'geo', 'geo_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('advert_offer_target_fk_updated_by', 'advert_offer_target', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');

//        $this->createTable('{{%advert_offer_target_log}}', [
//            'target_id' => $this->integer(11)->notNull(),
//            'user_id' => $this->integer(11)->notNull(),
//
//            'status_from_id' => $this->smallInteger(3)->null(),
//            'status_to_id' => $this->smallInteger(3)->null(),
//            'geo_from_id' => $this->integer(11)->null(),
//            'geo_to_id' => $this->integer(11)->null(),
//
//            'wm_visible' => $this->smallInteger(1)->null(),
//            'active' => $this->smallInteger(1)->null(),
//            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
//        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('advert_offer_target_fk_offer_id', 'advert_offer_target');
        $this->dropForeignKey('advert_offer_target_fk_geo_id', 'advert_offer_target');
        $this->dropForeignKey('advert_offer_target_fk_updated_by', 'advert_offer_target');
        $this->dropIndex('advert_offer_target_uq1', 'advert_offer_target');

        $this->dropTable('{{%advert_offer_target}}');
//        $this->dropTable('{{%advert_offer_target_log}}');
    }
}
