<?php

use yii\db\Migration;

/**
 * Handles the creation of table `customer_system`.
 */
class m130000_000001_customer_system extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%customer_system}}', [
            'customer_system_id' => $this->primaryKey(),
            'customer_id' => $this->integer(11)->notNull(),
            'ip' => $this->string(32)->null(),
            'country_id' => $this->integer(11)->null(),
            'os' => $this->string(32)->null(),
            'sid' => $this->string(32)->null(),
            'view_hash' => $this->string(32)->null(),
            'browser' => $this->string(255)->null(),
            'cookie' => $this->string(50)->null(),
        ], $tableOptions);

        $this->addForeignKey('customer_system_fk_customer_id', 'customer_system', 'customer_id', 'customer', 'customer_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('customer_system_fk_country_id', 'customer_system', 'country_id', 'countries', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('customer_system_fk_customer_id', 'customer_system');
        $this->dropForeignKey('customer_system_fk_country_id', 'customer_system');
        $this->dropTable('{{%customer_system}}');
    }
}
