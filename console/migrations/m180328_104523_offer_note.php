<?php

use yii\db\Migration;

/**
 * Class m180328_104523_offer_note
 */
class m180328_104523_offer_note extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('offer_note', [
            'offer_note_id'=>$this->primaryKey(),
            'offer_id' => $this->integer()->notNull(),
            'advert_id' => $this->integer()->notNull(),
            'geo_id' => $this->integer()->notNull(),
            'note' => $this->text(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->addForeignKey('offer_note_Fk_offer_id', 'offer_note', 'offer_id', 'offer', 'offer_id');
        $this->addForeignKey('offer_note_Fk_advert_id', 'offer_note', 'advert_id', 'user', 'id');
        $this->addForeignKey('offer_note_Fk_geo_id', 'offer_note', 'geo_id', 'countries', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('offer_note_Fk_offer_id', 'offer_note');
        $this->dropForeignKey('offer_note_Fk_advert_id', 'offer_note');
        $this->dropForeignKey('offer_note_Fk_geo_id', 'offer_note');

        $this->dropTable('offer_note');
    }

}
