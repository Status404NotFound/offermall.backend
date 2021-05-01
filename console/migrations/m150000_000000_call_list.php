<?php

use yii\db\Migration;

class m150000_000000_call_list extends Migration
{
    public function safeUp()
    {
        $this->createTable('call_list', [
            'order_id' => $this->primaryKey(4)->notNull(),
            'time_to_call' => $this->dateTime(),
            'attempts' => $this->integer(2),
            'lead_status' => $this->integer(2)->notNull(),
            'lead_state' => $this->integer(2)->notNull(),
            'language_id' => $this->integer(3)->notNull(),
            'operator_id' => $this->integer(4),
        ]);

        $this->addForeignKey('call_list_FK_order_id', 'call_list', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('call_list_FK_language_id', 'call_list', 'language_id', 'language', 'language_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('call_list_FK_operator_id', 'call_list', 'operator_id', 'user', 'id', 'NO ACTION', 'NO ACTION');

        $this->createIndex('call_list_Idx_lead_status', 'call_list', 'lead_status');
        $this->createIndex('call_list_Idx_lead_state', 'call_list', 'lead_state');
        $this->createIndex('call_list_idx_time_to_call', 'call_list', 'time_to_call');
    }

    public function safeDown()
    {
        $this->dropForeignKey('call_list_FK_order_id', 'call_list');
        $this->dropForeignKey('call_list_FK_language_id', 'call_list');
        $this->dropForeignKey('call_list_FK_operator_id', 'call_list');

        $this->dropIndex('call_list_Idx_lead_status', 'call_list');
        $this->dropIndex('call_list_Idx_lead_state', 'call_list');
        $this->dropIndex('call_list_idx_time_to_call', 'call_list');

        $this->dropTable('call_list');
    }
}
