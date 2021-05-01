<?php

use yii\db\Migration;

class m150000_100000_lead_calls extends Migration
{
    public function safeUp()
    {
        $this->createTable('lead_calls', [
            'id' => $this->primaryKey(),
            'operator_id' => $this->integer(5),
            'order_id' => $this->integer(5),
            'call_id' => $this->integer(5),
            'duration' => $this->integer(2),
            'datetime' => $this->dateTime(),
        ]);

        $this->createIndex('Index-operatorid_callid', 'lead_calls', ['operator_id', 'call_id'], true);
        $this->createIndex('Index-orderid', 'lead_calls', 'order_id');
    }

    public function safeDown()
    {
        $this->dropIndex('Index-operatorid_callid', 'lead_calls');
        $this->dropIndex('Index-orderid', 'lead_calls');
        $this->dropTable('lead_calls');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
