<?php

use yii\db\Migration;

/**
 * Class m180514_133416_operator_daily_table
 */
class m180514_133416_operator_daily_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%operator_daily}}', [
            'id' => $this->primaryKey(11),
            'operator_id' => $this->integer(5),
            'date' => $this->date()->unique(),
            'active_time' => $this->integer(11),
            'inactive_time' => $this->integer(11),
        ], $tableOptions);
    }
}
