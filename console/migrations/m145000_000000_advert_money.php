<?php

use yii\db\Migration;
use yii\db\Schema;


/**
 * Handles the creation of table `advert_money`.
 */
class m145000_000000_advert_money extends Migration
{
    public function safeUp()
    {
        // TODO
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%advert_money}}', [
            'advert_id' => $this->integer(11)->notNull(),
            'money' => $this->double(2)->notNull(),
            'currency_id' => $this->integer(11)->notNull(),

            'last_entrance_datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('idx-advert_money_advert_id', 'advert_money', 'advert_id', true);
        $this->addForeignKey('advert_money_fk_advert_id', 'advert_money', 'advert_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('advert_money_fk_currency_id', 'advert_money', 'currency_id', 'currency', 'currency_id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        // TODO
        $this->dropForeignKey('advert_money_fk_currency_id', 'advert_money');
        $this->dropForeignKey('advert_money_fk_advert_id', 'advert_money');
        $this->dropIndex('idx-advert_money_advert_id', 'advert_money');

        $this->dropTable('{{%advert_money}}');
    }
}
