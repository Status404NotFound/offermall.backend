<?php

use yii\db\Migration;
use yii\db\Schema;


/**
 * Handles the creation of table `declaration_print`.
 */
class m140002_000001_declaration_print extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%declaration_print}}', [
            'id' => $this->primaryKey(11),
            'order_id' => $this->integer(11)->null(),
            'declaration' => $this->string(255)->notNull(),

            'printed_by' => $this->integer(11)->notNull(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('idx-declaration_print_datetime', 'declaration_print', 'datetime');
        $this->addForeignKey('declaration_print_fk_order_id', 'declaration_print', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('declaration_print_fk_printed_by', 'declaration_print', 'printed_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('declaration_print_fk_order_id', 'declaration_print');
        $this->dropForeignKey('declaration_print_fk_printed_by', 'declaration_print');
        $this->dropIndex('idx-declaration_print_datetime', 'declaration_print');

        $this->dropTable('{{%declaration_print}}');
    }
}
