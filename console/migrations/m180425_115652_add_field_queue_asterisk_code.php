<?php

use yii\db\Migration;

/**
 * Class m180425_115652_add_field_queue_asterisk_code
 */
class m180425_115652_add_field_queue_asterisk_code extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('call_queue', 'queue_asterisk_code', 'varchar(255)');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }

}
