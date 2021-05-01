<?php

class m000000_300005_currency extends \yii\db\Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%currency}}', [
            'id' => $this->primaryKey(11),
            'currency_id' => $this->integer(11)->notNull(),
            'currency_name' => $this->string(255)->notNull(),
            'currency_code' => $this->string(7)->notNull(),
            'country_id' => $this->integer(11),
        ], $tableOptions);

        $this->createIndex('currency_uq1', 'currency', 'currency_id', true);
        $this->createIndex('currency_uq2', 'currency', 'currency_name', true);
        $this->createIndex('currency_uq3', 'currency', 'currency_code', true);
        $this->addForeignKey('currency_fk_country_id', 'currency', 'country_id', 'country', 'id', 'SET NULL', 'CASCADE');

        $this->insertData();
    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/sql/currency.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        $this->dropForeignKey('currency_fk_country_id', 'currency');
        $this->dropIndex('currency_uq1', 'currency');
        $this->dropIndex('currency_uq2', 'currency');
        $this->dropIndex('currency_uq3', 'currency');
        $this->dropTable('{{%currency}}');
    }
}
