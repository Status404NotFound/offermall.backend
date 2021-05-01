<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wm_checkboxes`.
 */
class m160000_400008_wm_checkboxes extends Migration
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

        $this->createTable('{{%wm_checkboxes}}', [
            'id' => $this->primaryKey(11),
            'wm_offer_id' => $this->integer(11)->notNull(),
            'websites' => $this->smallInteger(2),
            'doorway' => $this->smallInteger(2),
            'contextual_advertising' => $this->smallInteger(2),
            'for_the_brand' => $this->smallInteger(2),
            'teaser_advertising' => $this->smallInteger(2),
            'banner_advertising' => $this->smallInteger(2),
            'social_networks_targeting_ads' => $this->smallInteger(2),
            'games_applications' => $this->smallInteger(2),
            'email_marketing' => $this->smallInteger(2),
            'cash_back' => $this->smallInteger(2),
            'click_under' => $this->smallInteger(2),
            'motivated' => $this->smallInteger(2),
            'adult' => $this->smallInteger(2),
            'toolbar_traffic' => $this->smallInteger(2),
            'sms_sending' => $this->smallInteger(2),
            'spam' => $this->smallInteger(2),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',

        ], $tableOptions);

        $this->addForeignKey('wm_checkboxes_fk_wm_offer_id', 'wm_checkboxes', 'wm_offer_id', 'wm_offer', 'wm_offer_id', 'CASCADE', 'CASCADE');
//        $this->addForeignKey('wm_checkboxes_fk_wm_offer_id', 'wm_checkboxes', 'wm_offer_id', 'wm_offer', 'wm_offer_id', 'NO ACTION', 'NO ACTION');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('wm_checkboxes_fk_wm_offer_id', 'wm_checkboxes');

        $this->dropTable('{{%wm_checkboxes}}');
    }
}
