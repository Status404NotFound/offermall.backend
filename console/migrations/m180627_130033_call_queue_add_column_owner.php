<?php

use yii\db\Migration;

/**
 * Class m180627_130033_call_queue_add_column_owner
 */
class m180627_130033_call_queue_add_column_owner extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('call_queue', 'advert_id', 'integer');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }

}
