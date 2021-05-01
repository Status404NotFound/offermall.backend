<?php

use yii\db\Migration;

class m171129_113139_offer_geo extends Migration
{
    public function safeUp()
    {
        $this->createTable('offer_geo_price', [
            'offer_geo_price_id' => $this->primaryKey(),
            'offer_id' => $this->integer()->notNull(),
            'geo_id' => $this->integer()->notNull(),
            'new_price' => $this->string(),
            'old_price' => $this->string(),
            'discount' => $this->string(),
            'currency_id' => $this->integer(4),
        ]);

        $this->addForeignKey('offer_geo_price_Fk_offer_id', 'offer_geo_price', 'offer_id', 'offer', 'offer_id');
        $this->addForeignKey('offer_geo_price_Fk_geo_id', 'offer_geo_price', 'geo_id', 'geo', 'geo_id');
        $this->addForeignKey('offer_geo_price_Fk_currency_id', 'offer_geo_price', 'currency_id', 'currency', 'currency_id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('offer_geo_price_Fk_offer_id', 'offer_geo_price');
        $this->dropForeignKey('offer_geo_price_Fk_geo_id', 'offer_geo_price');
        $this->dropForeignKey('offer_geo_price_Fk_currency_id', 'offer_geo_price');
        $this->dropTable('offer_geo_price');
    }

}
