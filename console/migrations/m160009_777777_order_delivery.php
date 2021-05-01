<?php

use yii\db\Migration;

class m160009_777777_order_delivery extends Migration
{
    public function up()
    {
        $this->createTable('{{%order_delivery}}', [
            'id' => $this->primaryKey(20),

            'order_id' => $this->integer(11)->notNull(),
            'order_hash' => $this->bigInteger(20)->notNull(),
            'offer_id' => $this->integer(11)->notNull(),
            'sent_by' => $this->integer(11)->notNull(),

            'delivery_api_id' => $this->integer(11),
            'delivery_api_name' => $this->string(255)->null(),
            'user_api_id' => $this->integer(11),

            'tracking_no' => $this->string(255)->null(),
            'shipment_no' => $this->string(255)->null(),

            'remote_status' => $this->string(255)->null(),

            'shipment_data' => $this->string(255)->null(),
            'status_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',

            'money_in_fact' => $this->double(3)->notNull()->defaultValue(0.0),
            'currency_id' => $this->integer(11)->notNull(),

            'delivery_date_in_fact' => $this->timestamp()->null(),
            'report_no' => $this->string(255)->null(),
        ]);
        $this->addForeignKey('order_delivery_fk_user_api_id', 'order_delivery', 'user_api_id', 'user_delivery_api', 'api_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_delivery_fk_delivery_api_id', 'order_delivery', 'delivery_api_id', 'delivery_api', 'delivery_api_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_delivery_fk_order_id', 'order_delivery', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_delivery_fk_offer_id', 'order_delivery', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_delivery_fk_sent_by', 'order_delivery', 'sent_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('order_delivery_fk_currency_id', 'order_delivery', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');

    }

    public function down()
    {
        $this->dropForeignKey('order_delivery_fk_user_api_id', 'order_delivery');
        $this->dropForeignKey('order_delivery_fk_delivery_api_id', 'order_delivery');
        $this->dropForeignKey('order_delivery_fk_order_id', 'order_delivery');
        $this->dropForeignKey('order_delivery_fk_offer_id', 'order_delivery');
        $this->dropForeignKey('order_delivery_fk_sent_by', 'order_delivery');
        $this->dropForeignKey('order_delivery_fk_currency_id', 'order_delivery');
        $this->dropTable('{{%order_delivery}}');
    }
}
