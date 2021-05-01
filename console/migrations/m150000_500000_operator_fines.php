<?php

use yii\db\Migration;

class m150000_500000_operator_fines extends Migration
{
    public function safeUp()
    {
        $this->createTable('operator_fine', [
            'operator_fine_id' => $this->primaryKey(),
            'operator_id' => $this->integer(11)->notNull(),
            'status_id' => $this->integer(3)->notNull(),
            'created_at' => $this->timestamp(),
        ]);

        $this->addForeignKey('operator_fine_Fk_operator_id', 'operator_fine', 'operator_id', 'user', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('operator_fine_Fk_operator_id', 'operator_fine');
        $this->dropTable('operator_fine');
    }

}
