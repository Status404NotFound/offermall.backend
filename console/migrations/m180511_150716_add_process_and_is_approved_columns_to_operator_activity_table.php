<?php

use yii\db\Migration;

/**
 * Class m180511_150716_add_process_and_is_approved_columns_to_operator_activity_table
 */
class m180511_150716_add_process_and_is_approved_columns_to_operator_activity_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('operator_activity', 'process', $this->integer());
        $this->addColumn('operator_activity', 'is_approved', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('operator_activity', 'process');
        $this->dropColumn('operator_activity', 'is_approved');
    }
}
