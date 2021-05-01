<?php

use yii\db\Schema;
use yii\db\Migration;

class m100000_000000_product extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%product}}', [
            'product_id' => $this->primaryKey(11),
            'product_name' => $this->string(255)->notNull(),

            'category' => $this->smallInteger(3)->defaultValue(1),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11)->notNull(),

            'img' => $this->string(255)->null(),
            'description' => $this->text()->null(),
            'visible' => $this->smallInteger(1)->defaultValue(1),

        ], $tableOptions);

        $this->createIndex('product_uq1', 'product', 'product_name', true);
        $this->addForeignKey('product_fk_created_by', 'product', 'created_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('product_fk_updated_by', 'product', 'updated_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('product_fk_created_by', 'product');
        $this->dropForeignKey('product_fk_updated_by', 'product');
        $this->dropIndex('product_uq1', 'product');

        $this->dropTable('{{%product}}');
    }
}
