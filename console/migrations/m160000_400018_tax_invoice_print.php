<?php

use yii\db\Migration;

/**
 * Class m600000_400018_tax_invoice_print
 */
class m160000_400018_tax_invoice_print extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%tax_invoice_print}}', [
            'id' => $this->primaryKey(11),
            'order_id' => $this->integer(11)->null(),
            'tax_invoice' => $this->string(255)->notNull(),

            'printed_by' => $this->integer(11)->notNull(),
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('idx-tax_invoice_print_datetime', 'tax_invoice_print', 'datetime');
        $this->addForeignKey('tax_invoice_print_fk_order_id', 'tax_invoice_print', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('tax_invoice_print_fk_printed_by', 'tax_invoice_print', 'printed_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('tax_invoice_print_fk_order_id', 'tax_invoice_print');
        $this->dropForeignKey('tax_invoice_print_fk_printed_by', 'tax_invoice_print');
        $this->dropIndex('idx-tax_invoice_print_datetime', 'tax_invoice_print');

        $this->dropTable('{{%tax_invoice_print}}');
    }
}
