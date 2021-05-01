<?php


class m000000_300001_geo extends \yii\db\Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%geo}}', [
            'id' => $this->primaryKey(11),
            'geo_id' => $this->integer(11)->notNull(),
            'geo_name' => $this->string(255)->notNull(),
            'iso' => $this->string(3)->notNull(),
            'phone_code' => $this->integer(5)->notNull(),
        ], $tableOptions);

        $this->createIndex('geo_uq1', 'geo', 'geo_id', true);
        $this->createIndex('geo_uq2', 'geo', 'geo_name', true);
        $this->addForeignKey('geo_fk_country_id', 'geo', 'geo_id', 'countries', 'id', 'NO ACTION', 'CASCADE');

        $this->insertData();
    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/sql/geo.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        $this->dropForeignKey('geo_fk_country_id', 'geo');
        $this->dropIndex('geo_uq1', 'geo');
        $this->dropIndex('geo_uq2', 'geo');
        $this->dropTable('{{%geo}}');
    }
}
