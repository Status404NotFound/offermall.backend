<?php

use yii\db\Migration;

/**
 * Class m600000_400011_steal_data_sent
 */
class m160000_400011_steal_data_sent extends Migration
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

        $this->createTable('steal_data_sent', [
            'site_id' => $this->primaryKey(11),
            'site' => $this->string(255)->notNull(),
            'date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'status' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('steal_data_sent');
    }
}
