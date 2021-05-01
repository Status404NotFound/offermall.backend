<?php

use yii\db\Migration;

/**
 * Class m600000_400014_black_list_attempt
 */
class m160000_400014_black_list_attempt extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('black_list_attempt', [
            'black_list_attempt_id' => $this->primaryKey(),
            'date' => $this->dateTime()->notNull(),
            'customer_black_list_id' => $this->integer(11)->notNull(),
            'status_id' => $this->integer(11)->notNull(),
            'reason_id' => $this->integer(11)->notNull(),
            'attempts' => $this->integer(11),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',

        ], $tableOptions);

        $this->createIndex('black_list_attempt_index_date', 'black_list_attempt', 'date');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('black_list_attempt_index_date', 'black_list_attempt');

        $this->dropTable('black_list_attempt');
    }
}
