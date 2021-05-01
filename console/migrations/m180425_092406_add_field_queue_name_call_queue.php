<?php

use yii\db\Migration;

/**
 * Class m180425_092406_add_field_queue_name_call_queue
 */
class m180425_092406_add_field_queue_name_call_queue extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('call_queue', 'queue_name', 'varchar(255)');
    }

    public function safeDown()
    {

    }
}
