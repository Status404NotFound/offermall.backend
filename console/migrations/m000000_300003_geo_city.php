<?php

class m000000_300003_geo_city extends \yii\db\Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%geo_city}}', [
            'city_id' => $this->primaryKey(11),
            'city_name' => $this->string(255)->notNull(),
            'region_id' => $this->integer(11)->null(),
            'geo_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('geo_city_uq1', 'geo_city', 'city_name', true);
        $this->addForeignKey('geo_city_fk_geo_id', 'geo_city', 'geo_id', 'geo', 'geo_id', 'NO ACTION', 'CASCADE');
        $this->addForeignKey('geo_city_fk_geo_region_id', 'geo_city', 'region_id', 'geo_region', 'region_id', 'SET NULL', 'CASCADE');

        $this->insertData();
    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/sql/geo_city.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        $this->dropIndex('geo_city_uq1', 'geo_city');
        $this->dropForeignKey('geo_city_fk_geo_region_id', 'geo_city');
        $this->dropForeignKey('geo_city_fk_geo_id', 'geo_city');
        $this->dropTable('{{%geo_city}}');
    }
}
