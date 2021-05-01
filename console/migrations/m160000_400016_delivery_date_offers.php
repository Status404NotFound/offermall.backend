<?php

use yii\db\Migration;

/**
 * Class m600000_400016_delivery_date_offers
 */
class m160000_400016_delivery_date_offers extends Migration
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
        $this->createTable('delivery_date_offers', [
            'delivery_date_offer_id' => $this->primaryKey(11),
            'delivery_date_id' => $this->integer(11)->notNull(),
            'offer_id' => $this->integer(11)->notNull(),
            'geo_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-delivery_date_offers-delivery_date_id', 'delivery_date_offers', 'delivery_date_id');
        $this->createIndex('idx-delivery_date_offers-offer_id', 'delivery_date_offers', 'offer_id');
        $this->createIndex('idx-delivery_date_offers-geo_id', 'delivery_date_offers', 'geo_id');

        $this->addForeignKey('fk-delivery_date_offers-delivery_date', 'delivery_date_offers', 'delivery_date_id', 'delivery_date', 'delivery_date_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-delivery_date_offers-offer', 'delivery_date_offers', 'offer_id', 'offer', 'offer_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-delivery_date_offers-geo', 'delivery_date_offers', 'geo_id', 'geo', 'geo_id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-delivery_date_offers-delivery_date_id', 'delivery_date_offers');
        $this->dropIndex('idx-delivery_date_offers-offer_id', 'delivery_date_offers');
        $this->dropIndex('idx-delivery_date_offers-geo_id', 'delivery_date_offers');

        $this->dropForeignKey('fk-delivery_date_offers-delivery_date', 'delivery_date_offers');
        $this->dropForeignKey('fk-delivery_date_offers-offer', 'delivery_date_offers');
        $this->dropForeignKey('fk-delivery_date_offers-geo', 'delivery_date_offers');

        $this->dropTable('delivery_date_offers');
    }
}
