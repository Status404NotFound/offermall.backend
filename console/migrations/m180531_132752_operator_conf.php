<?php

use yii\db\Migration;

class m180531_132752_operator_conf extends Migration
{
    public function safeUp()
    {
        $this->createTable('operator_conf', [
            'id' => $this->primaryKey(),
            'operator_id' => $this->integer(4)->notNull(),
            'call_mode' => $this->boolean()->notNull(),
            'status' => \yii\db\mysql\Schema::TYPE_SMALLINT,
            'sip' => $this->integer(3)->notNull(),
            'channel' => $this->integer(3)->notNull(),
        ]);

        $this->createIndex('idx-operator_conf_operator_id', 'operator_conf', 'operator_id');
        $this->createIndex('idx-operator_conf_status', 'operator_conf', 'status');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-operator_conf_operator_id', 'operator_conf');
        $this->dropIndex('idx-operator_conf_status', 'operator_conf');
        $this->dropTable('operator_conf');
    }
}
