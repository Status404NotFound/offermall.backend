<?php

use yii\db\Migration;

/**
 * Handles the creation of table `offer_product`.
 */
class m120000_000001_offer_product extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%offer_product}}', [
            'id' => $this->primaryKey(11),
            'offer_id' => $this->integer(11)->notNull(),
            'product_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->addForeignKey('offer_product_fk_offer_id', 'offer_product', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('offer_product_fk_prod_id', 'offer_product', 'product_id', 'product', 'product_id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('offer_product_fk_offer_id', 'offer_product');
        $this->dropForeignKey('offer_product_fk_prod_id', 'offer_product');

        $this->dropTable('{{%offer_product}}');
    }
}
