<?php

use yii\db\Migration;

class m150000_700000_operator_lines_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('operator_lines', [
            'id' => $this->primaryKey(),
            'line_id' => $this->integer(4),
            'operator_id' => $this->integer(4),
            'is_active' => $this->boolean(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->createIndex('Index-operatorid', 'operator_lines', 'operator_id');
    }

    public function safeDown()
    {
        $this->dropIndex('Index-operatorid', 'operator_lines');
        $this->dropTable( 'operator_lines');
    }
}
