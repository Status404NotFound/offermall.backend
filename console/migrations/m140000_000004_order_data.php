<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order`.
 */
class m140000_000004_order_data extends Migration
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

        $this->createTable('order_data', [
            'order_data_id' => $this->primaryKey(11),

            'order_id' => $this->integer(11)->notNull(),
            'order_hash' => $this->bigInteger(20),
            'offer_id' => $this->integer(11)->null(),
            'owner_id' => $this->integer(11)->null(),
            'wm_id' => $this->integer(11)->null(),

            'fields' => $this->text()->null(),
            'view_time' => $this->string(255)->null(),
            'view_hash' => $this->string(255)->null(),
            'referrer' => $this->string(255)->null(),

            'sub_id_1' => $this->string(255)->null(),
            'sub_id_2' => $this->string(255)->null(),
            'sub_id_3' => $this->string(255)->null(),
            'sub_id_4' => $this->string(255)->null(),
            'sub_id_5' => $this->string(255)->null(),

            'declaration' => $this->string(255)->null(),

            'updated_by' => $this->integer()->null(),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',

            'comment' => $this->text()->null(),
        ], $tableOptions);

        $this->addForeignKey('order_data_fk_order_id', 'order_data', 'order_id', 'order', 'order_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('order_data_fk_offer_id', 'order_data', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_data_fk_owner_id', 'order_data', 'owner_id', 'user', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('order_data_fk_wm_id', 'order_data', 'wm_id', 'user', 'id', 'SET NULL', 'SET NULL');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('order_data_fk_order_id', 'order_data');
        $this->dropForeignKey('order_data_fk_offer_id', 'order_data');
        $this->dropForeignKey('order_data_fk_wm_id', 'order_data');
        $this->dropForeignKey('order_data_fk_owner_id', 'order_data');

        $this->dropTable('order_data');
    }
}
