<?php

use yii\db\Migration;

/**
 * Class m180627_124721_call_list_add_queue_column
 */
class m180627_124721_call_list_add_queue_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('call_list', 'queue_id', 'integer');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }

}
