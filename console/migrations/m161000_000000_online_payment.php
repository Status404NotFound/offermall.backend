<?php

use yii\db\Migration;

class m161000_000000_online_payment extends Migration
{
    public function up()
    {
        $this->createTable('{{%online_payment}}', [
            'id' => $this->primaryKey(20),

            'order_id' => $this->integer(11)->notNull(),
            'order_hash' => $this->bigInteger(20)->notNull(),
            'offer_id' => $this->integer(11)->notNull(),

            'amount' => $this->double(3),
            'currency_id' => $this->integer(11)->notNull(),
            'currency_name' => $this->string(20)->null(),

            'tracking_id' => $this->string(255),
            'bank_ref_no' => $this->string(255)->null(),

            'payment_name' => $this->string(255)->null(),
            'payment_status' => $this->string(255)->null(),
            'message' => $this->text()->null(),

            'serialized_data' => $this->text()->notNull(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
        $this->addForeignKey('online_payment_fk_order_id', 'online_payment', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('online_payment_fk_offer_id', 'online_payment', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('online_payment_fk_currency_id', 'online_payment', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');
    }

    public function down()
    {
        $this->dropForeignKey('online_payment_fk_order_id', 'online_payment');
        $this->dropForeignKey('online_payment_fk_offer_id', 'online_payment');
        $this->dropForeignKey('online_payment_fk_currency_id', 'online_payment');
        $this->dropTable('{{%online_payment}}');
    }
}
