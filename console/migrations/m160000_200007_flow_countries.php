<?php

use yii\db\Migration;

class m160000_200007_flow_countries extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%flow_countries}}', [
            'id' => $this->primaryKey(11),
            'flow_id' => $this->integer(11)->notNull(),
            'country_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('flow_countries_uq1', 'flow_countries', ['flow_id', 'country_id']);
        $this->addForeignKey('flow_countries_fk_flow_id', 'flow_countries', 'flow_id', 'flow', 'flow_id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('flow_countries_fk_flow_id', 'flow_countries');
        $this->dropIndex('flow_countries_uq1', 'flow_countries');

        $this->dropTable('{{%flow_countries}}');
    }
}
