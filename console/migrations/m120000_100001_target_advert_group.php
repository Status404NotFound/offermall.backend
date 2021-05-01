<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_advert_group`.
 */
class m120000_100001_target_advert_group extends Migration
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

        $this->createTable('{{%target_advert_group}}', [
            'target_advert_group_id' => $this->primaryKey(11),
            'advert_offer_target_id' => $this->integer(11)->notNull(),

            'daily_limit' => $this->integer(7)->null(),
            'currency_id' => $this->integer(11)->notNull(),

            'base_commission' => $this->double()->null(),
            'exceeded_commission' => $this->double()->null(),
            'use_commission_rules' => $this->smallInteger(1)->defaultValue(0),

            'send_sms_customer' => $this->smallInteger(1)->defaultValue(0),
            'sms_text_customer' => $this->string(255)->null(),

            'send_sms_owner' => $this->smallInteger(1)->defaultValue(0),
            'sms_text_owner' => $this->string(255)->null(),

            'active' => $this->smallInteger(2)->defaultValue(0),

            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);


        $this->addForeignKey('target_advert_group_fk_advert_offer_target_id', 'target_advert_group', 'advert_offer_target_id', 'advert_offer_target', 'advert_offer_target_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_group_fk_currency_id', 'target_advert_group', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('target_advert_group_fk_updated_by', 'target_advert_group', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_advert_group_fk_advert_offer_target_id', 'target_advert_group');
        $this->dropForeignKey('target_advert_group_fk_currency_id', 'target_advert_group');
        $this->dropForeignKey('target_advert_group_fk_updated_by', 'target_advert_group');

        $this->dropTable('{{%target_advert_group}}');
    }
}
