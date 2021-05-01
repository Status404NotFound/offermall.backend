<?php

use yii\db\Migration;

/**
 * Handles the creation of table `offer`.
 */
class m120000_000000_offer extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%offer}}', [
            'offer_id' => $this->primaryKey(11),
            'offer_hash' => $this->string(32)->null(),

//            'product_id' => $this->integer(11)->notNull(),

            'offer_name' => $this->string(255)->notNull(),
            'offer_status' => $this->smallInteger(3)->defaultValue(1),

            'description' => $this->text()->null(),
            'img' => 'LONGBLOB',

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-offer_offer_hash', 'offer', 'offer_hash');
//        $this->addForeignKey('offer_fk_product_id', 'offer', 'product_id', 'product', 'product_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('offer_fk_created_by', 'offer', 'created_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('offer_fk_updated_by', 'offer', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
//        $this->dropForeignKey('offer_fk_product_id', 'offer');
        $this->dropForeignKey('offer_fk_created_by', 'offer');
        $this->dropForeignKey('offer_fk_updated_by', 'offer');
        $this->dropIndex('idx-offer_offer_hash', 'offer');

        $this->dropTable('{{%offer}}');
    }
}
