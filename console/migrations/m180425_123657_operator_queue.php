<?php

use yii\db\Migration;

/**
 * Class m180425_123657_operator_queue
 */
class m180425_123657_operator_queue extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('operator_queue', [
            'operator_queue_id' => $this->primaryKey(),
            'operator_id' => $this->integer()->notNull(),
            'call_queue_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('operator_queue_Fk_call_queue_id', 'operator_queue', 'call_queue_id', 'call_queue', 'queue_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('operator_queue_Fk_call_queue_id', 'operator_queue');
        $this->dropTable('operator_queue');
    }

}
