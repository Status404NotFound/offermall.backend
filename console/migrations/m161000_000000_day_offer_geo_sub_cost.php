<?php

use yii\db\Migration;

class m161000_000000_day_offer_geo_sub_cost extends Migration
{
    public function up()
    {
        $this->createTable('{{%day_offer_geo_sub_cost}}', [
            'id' => $this->primaryKey(20),

            'offer_id' => $this->integer(11)->notNull(),
            'geo_id' => $this->integer(11)->notNull(),
            'known_sub_id' => $this->integer(255)->notNull(),

            'date' => 'timestamp NOT NULL',

            'sub_id_1' => $this->string(255)->null(),

            'sum' => $this->double(3)->notNull(),
            'currency_id' => $this->integer(11)->notNull(),
            'rate' => $this->double(3)->notNull(),
            'usd_sum' => $this->double(3)->notNull(),

            'created_by' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11)->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
        $this->addForeignKey('day_offer_geo_sub_cost_fk_offer_id', 'day_offer_geo_sub_cost', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('day_offer_geo_sub_cost_fk_geo_id', 'day_offer_geo_sub_cost', 'geo_id', 'geo', 'geo_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('day_offer_geo_sub_cost_fk_known_sub_id', 'day_offer_geo_sub_cost', 'known_sub_id', 'known_sub_id_1', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('day_offer_geo_sub_cost_fk_currency_id', 'day_offer_geo_sub_cost', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('day_offer_geo_sub_cost_fk_created_by', 'day_offer_geo_sub_cost', 'created_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('day_offer_geo_sub_cost_fk_updated_by', 'day_offer_geo_sub_cost', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->createIndex('idx-day_offer_geo_sub_cost', 'day_offer_geo_sub_cost', 'date');
    }

    public function down()
    {
        $this->dropIndex('idx-day_offer_geo_sub_cost', 'day_offer_geo_sub_cost');
        $this->dropForeignKey('day_offer_geo_sub_cost_fk_offer_id', 'day_offer_geo_sub_cost');
        $this->dropForeignKey('day_offer_geo_sub_cost_fk_geo_id', 'day_offer_geo_sub_cost');
        $this->dropForeignKey('day_offer_geo_sub_cost_fk_known_sub_id', 'day_offer_geo_sub_cost');
        $this->dropForeignKey('day_offer_geo_sub_cost_fk_currency_id', 'day_offer_geo_sub_cost');
        $this->dropForeignKey('day_offer_geo_sub_cost_fk_created_by', 'day_offer_geo_sub_cost');
        $this->dropForeignKey('day_offer_geo_sub_cost_fk_updated_by', 'day_offer_geo_sub_cost');
        $this->dropTable('{{%day_offer_geo_sub_cost}}');
    }
}
