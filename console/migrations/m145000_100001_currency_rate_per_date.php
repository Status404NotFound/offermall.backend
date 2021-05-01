<?php

use yii\db\Migration;

/**
 * Handles the creation of table `currency_rate_per_date`.
 */
class m145000_100001_currency_rate_per_date extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%currency_rate_per_date}}', [
            'rate_id' => $this->primaryKey(11)->notNull(),
            'rate' => $this->double(3)->notNull(),
            'currency_id' => $this->integer(11)->notNull(),
            'date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->addForeignKey('currency_rate_per_date_fk_currency_id', 'currency_rate_per_date', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('currency_rate_per_date_fk_currency_id', 'currency_rate_per_date');
        $this->dropTable('{{%currency_rate_per_date}}');
    }
}
