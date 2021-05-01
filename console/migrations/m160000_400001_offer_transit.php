<?php

use yii\db\Migration;

class m160000_400001_offer_transit extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%offer_transit}}', [
            'transit_id' => $this->primaryKey(11),
            'url' => $this->string(255),
            'name' => $this->string(255),
            'offer_id' => $this->integer(11),
        ], $tableOptions);

        $this->addForeignKey('offer_transit_fk_offer_id', 'offer_transit', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');

    }

    public function safeDown()
    {
        $this->dropForeignKey('offer_transit_fk_offer_id', 'offer_transit');
        $this->dropTable('offer_transit');
    }
}
