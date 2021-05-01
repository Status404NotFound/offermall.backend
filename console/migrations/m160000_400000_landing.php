<?php

use yii\db\Migration;

class m160000_400000_landing extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('landing', [
            'landing_id' => $this->primaryKey(11),
            'name' => $this->string(255)->notNull(),
            'url' => $this->string(255)->notNull(),
            'offer_id' => $this->integer(11)->notNull(),
            'form_id' => $this->integer(11)->notNull(),
        ], $tableOptions);


        $this->addForeignKey('landing_fk_offer_id', 'landing', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('landing_fk_form_id', 'landing', 'form_id', 'gen_form_table', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('landing_fk_offer_id', 'landing');
        $this->dropForeignKey('landing_fk_form_id', 'landing');
        $this->dropTable('landing');
    }
}
