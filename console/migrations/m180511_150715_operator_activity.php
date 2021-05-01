<?php

use yii\db\Migration;

class m180511_150715_operator_activity extends Migration
{
    public function up()
    {
        $this->createTable('operator_activity', [
            'id' => $this->primaryKey(),
            'operator_id' => $this->integer(5),
            'operator_status' => $this->integer(3),
            'status_time_start' => $this->dateTime(),
            'status_time_finish' => $this->dateTime(),
        ]);
    }

    public function down()
    {
        $this->dropTable('operator_activity');

//        echo "m170320_110241_operator_activity cannot be reverted.\n";
//        return false;
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
