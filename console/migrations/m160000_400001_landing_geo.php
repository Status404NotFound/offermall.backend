<?php

use yii\db\Migration;

class m160000_400001_landing_geo extends Migration
{
    public function up()
    {
        $this->createTable('landing_geo', [
            'landing_geo_id' => $this->primaryKey(),
            'landing_id' => $this->integer(11)->notNull(),
            'geo_id' => $this->integer(4)->notNull(),
            'old_price' => $this->string(11)->notNull(),
            'new_price' => $this->string(11)->notNull(),
            'discount' => $this->string(11)->notNull(),
            'currency_id' => $this->integer(4)->notNull(),
        ]);

        $this->addForeignKey('landing_geo_fk_currency_id', 'landing_geo', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('landing_geo_fk_geo_id', 'landing_geo', 'geo_id', 'geo', 'geo_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('landing_geo_fk_landing_id', 'landing_geo', 'landing_id', 'landing', 'landing_id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('landing_geo_fk_currency_id', 'landing_geo');
        $this->dropForeignKey('landing_geo_fk_geo_id', 'landing_geo');
        $this->dropForeignKey('landing_geo_fk_landing_id', 'landing_geo');
        $this->dropTable('landing_geo');
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
