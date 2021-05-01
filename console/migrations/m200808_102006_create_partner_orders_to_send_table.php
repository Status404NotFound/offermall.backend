<?php

use yii\db\Migration;

/**
 * Handles the creation of table `partner_orders_to_send`.
 */
class m200808_102006_create_partner_orders_to_send_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('partner_orders_to_send', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(11)->notNull(),
            'partner_id' => $this->integer(11)->notNull(),
            'iso' => $this->string(5),
        ]);

        $this->createIndex('partner_orders_to_send_Idx_order_id_partner_id', 'partner_orders_to_send', ['order_id', 'partner_id'], true);

        $this->addForeignKey('partner_orders_to_send_fk_order_id', 'partner_orders_to_send', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('partner_orders_to_send_fk_partner_id', 'partner_orders_to_send', 'partner_id', 'partner_crm', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('partner_orders_to_send_fk_order_id', 'partner_orders_to_send');
        $this->dropForeignKey('partner_orders_to_send_fk_partner_id', 'partner_orders_to_send');

        $this->dropTable('partner_orders_to_send');
    }
}
