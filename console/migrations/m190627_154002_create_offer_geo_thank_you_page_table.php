<?php

use yii\db\Migration;

/**
 * Handles the creation of table `offer_geo_thank_you_page`.
 */
class m190627_154002_create_offer_geo_thank_you_page_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('offer_geo_thank_you_page', [
            'id' => $this->primaryKey(),
            'offer_id' => $this->integer(11)->notNull(),
            'geo_id' => $this->integer(11)->notNull(),
            'url' => $this->text()->notNull(),
        ]);

        $this->addForeignKey('offer_geo_thank_you_page_fk_offer_id', 'offer_geo_thank_you_page', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('offer_geo_thank_you_page_fk_geo_id', 'offer_geo_thank_you_page', 'geo_id', 'geo', 'geo_id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('offer_geo_thank_you_page_fk_offer_id', 'offer_geo_thank_you_page');
        $this->dropForeignKey('offer_geo_thank_you_page_fk_geo_id', 'offer_geo_thank_you_page');
        $this->dropTable('offer_geo_thank_you_page');
    }
}
