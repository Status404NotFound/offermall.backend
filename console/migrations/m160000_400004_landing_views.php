<?php

use yii\db\Migration;

class m160000_400004_landing_views extends Migration
{
    public function safeUp()
    {
        $this->createTable('landing_views', [
            'landing_views_id' => $this->primaryKey(),
            'date' => $this->dateTime()->notNull(),
            'landing_id' => $this->integer(11),
            'flow_id' => $this->integer(11),
            'offer_id' => $this->integer(11),
            'geo_id' => $this->integer(4),
            'views' => $this->integer(11),
            'uniques' => $this->integer(),
            'sub_id_1' => $this->string(15),
            'sub_id_2' => $this->string(15),
            'sub_id_3' => $this->string(15),
            'sub_id_4' => $this->string(15),
            'sub_id_5' => $this->string(15),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',

        ]);

        $this->addForeignKey('landing_views_fk_offer_id', 'landing_views', 'offer_id', 'offer', 'offer_id');
        $this->addForeignKey('landing_views_fk_flow_id', 'landing_views', 'flow_id', 'flow', 'flow_id');
        $this->createIndex('landing_views_Index_date', 'landing_views', 'date');
    }

    public function safeDown()
    {
        $this->dropForeignKey('landing_views_fk_offer_id', 'landing_views');
        $this->dropForeignKey('landing_views_fk_flow_id', 'landing_views');
        $this->dropIndex('landing_views_Index_date', 'landing_views');

        $this->dropTable('landing_views');
    }
}
