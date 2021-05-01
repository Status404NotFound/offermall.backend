<?php

use yii\db\Migration;
use yii\db\Schema;


/**
 * Handles the creation of table `advert_money_entrance`.
 */
class m145000_000001_advert_money_entrance extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%advert_money_entrance}}', [
            'advert_id' => $this->integer(11)->notNull(),
            'old_sum' => $this->double(2)->notNull()->defaultValue(0),
            'sum' => $this->double(2)->notNull(),
            'comment' => $this->string(255),
            'added_by' => $this->integer(11)->notNull(),
            'entrance_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'datetime' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('idx-advert_money_entrance_datetime', 'advert_money_entrance', 'datetime');
        $this->addForeignKey('advert_money_entrance_fk_advert_id', 'advert_money_entrance', 'advert_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('advert_money_entrance_fk_added_by', 'advert_money_entrance', 'added_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('advert_money_entrance_fk_advert_id', 'advert_money_entrance');
        $this->dropForeignKey('advert_money_entrance_fk_added_by', 'advert_money_entrance');
        $this->dropIndex('idx-advert_money_entrance_datetime', 'advert_money_entrance');

        $this->dropTable('{{%advert_money_entrance}}');
    }
}
