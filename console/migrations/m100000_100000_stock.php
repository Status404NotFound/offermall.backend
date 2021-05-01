<?php

use yii\db\Migration;

/**
 * Handles the creation of table `stock`.
 */
class m100000_100000_stock extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%stock}}', [
            'stock_id' => $this->primaryKey(11),
            'owner_id' => $this->integer(11)->notNull(),
            'stock_name' => $this->string(255)->notNull(),
            'location' => $this->integer(11)->notNull(),
            'status' => $this->smallInteger(3)->notNull(),
        ], $tableOptions);
        $this->createIndex('stock_uq1', 'stock', 'stock_name', true);
        $this->addForeignKey('stock_fk_owner_id', 'stock', 'owner_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('stock_fk_location', 'stock', 'location', 'geo', 'geo_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('stock_uq1', 'stock');
        $this->dropForeignKey('stock_fk_owner_id', 'stock');
        $this->dropForeignKey('stock_fk_location', 'stock');
        $this->dropTable('{{%stock}}');
    }
}
