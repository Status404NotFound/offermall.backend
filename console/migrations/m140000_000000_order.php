<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order`.
 */
class m140000_000000_order extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('order', [
            'order_id' => $this->primaryKey(11),
            'order_hash' => $this->bigInteger(20),

            'offer_id' => $this->integer(11)->notNull(),
            'target_advert_id' => $this->integer(11)->null(),
            'target_wm_id' => $this->integer(11)->null(),
            'flow_id' => $this->integer(11)->null(),
            'customer_id' => $this->integer(11)->notNull(),

            'order_status' => $this->smallInteger(3)->defaultValue(0),
            'status_reason' => $this->integer(11)->null(),
            'delivery_date' => $this->timestamp()->null(),

            'total_amount' => $this->integer(5)->null(),
            'total_cost' => $this->double(3)->null(),
            'usd_total_cost' => $this->double(3)->null(),

            'advert_commission' => $this->double(3)->null(),
            'usd_advert_commission' => $this->double(3)->null(),

            'wm_commission' => $this->double(3)->null(),
            'usd_wm_commission' => $this->double(3)->null(),

            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),

            'session_id' => $this->string(32)->null(),
            'is_autolead' => $this->boolean()->defaultValue(0),
            'bitrix_flag' => $this->boolean()->defaultValue(1),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',

            'comment' => $this->text()->null(),
            'information' => $this->string(255)->null(),
            'deleted' => $this->smallInteger(2)->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('idx-order_created_at', 'order', 'created_at');
        $this->createIndex('idx-order_updated_at', 'order', 'updated_at');
        $this->createIndex('idx-order_session_id', 'order', 'session_id');

        $this->addForeignKey('order_fk_offer_id', 'order', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_fk_target_advert_id', 'order', 'target_advert_id', 'target_advert', 'target_advert_id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('order_fk_target_wm_id', 'order', 'target_wm_id', 'target_wm', 'target_wm_id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('order_fk_customer_id', 'order', 'customer_id', 'customer', 'customer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_fk_flow_id', 'order', 'flow_id', 'flow', 'flow_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('order_fk_offer_id', 'order');
        $this->dropForeignKey('order_fk_customer_id', 'order');
        $this->dropForeignKey('order_fk_flow_id', 'order');
        $this->dropForeignKey('order_fk_target_advert_id', 'order');
        $this->dropForeignKey('order_fk_target_wm_id', 'order');

        $this->dropIndex('idx-order_session_id', 'order');
        $this->dropIndex('idx-order_created_at', 'order');
        $this->dropIndex('idx-order_updated_at', 'order');

        $this->dropTable('order');
    }
}
