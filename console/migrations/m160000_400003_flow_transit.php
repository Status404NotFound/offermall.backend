<?php

use yii\db\Migration;

class m160000_400003_flow_transit extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%flow_transit}}', [
            'id' => $this->primaryKey(11),
            'flow_id' => $this->integer(11)->notNull(),
            'transit_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->addForeignKey('flow_transit_fk_flow_id', 'flow_transit', 'flow_id', 'flow', 'flow_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('flow_transit_fk_transit_id', 'flow_transit', 'transit_id', 'offer_transit', 'transit_id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('flow_transit_fk_flow_id', 'flow_transit');
        $this->dropForeignKey('flow_transit_fk_transit_id', 'flow_transit');

        $this->dropTable('{{%flow_transit}}');
    }
}
