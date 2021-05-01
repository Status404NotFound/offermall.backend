<?php


class m000000_300002_geo_region extends \yii\db\Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%geo_region}}', [
            'region_id' => $this->primaryKey(11),
            'region_name' => $this->string(255)->notNull(),
            'geo_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('geo_region_uq1', 'geo_region', 'region_name', true);
        $this->createIndex('geo_region_uq2', 'geo_region', ['geo_id', 'region_id'], true);
        $this->addForeignKey('geo_region_fk_geo_id', 'geo_region', 'geo_id', 'geo', 'geo_id', 'NO ACTION', 'CASCADE');

        $this->insertData();
    }

    public function insertData()
    {
        $query = file_get_contents(__DIR__ . '/sql/geo_region.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        $this->dropForeignKey('geo_region_fk_geo_id', 'geo_region');
        $this->dropIndex('geo_region_uq1', 'geo_region');
        $this->dropIndex('geo_region_uq2', 'geo_region');

        $this->dropTable('{{%geo_region}}');
    }
}
