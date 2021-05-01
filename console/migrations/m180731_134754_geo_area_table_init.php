<?php

use common\models\geo\GeoArea;
use common\models\geo\GeoRegion;
use yii\db\Migration;

/**
 * Class m180731_134754_geo_area_table_init
 */
class m180731_134754_geo_area_table_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%geo_area}}', [
            'area_id' => $this->primaryKey(11),
            'main_area' => $this->string(255)->null(),
            'sub_area' => $this->string(255)->null(),
            'area_known_locally' => $this->string(255)->null(),
            'region_id' => $this->integer(11)->notNull(),
        ]);
    
        $this->createIndex('geo_area_area_id_uindex', 'geo_area', 'area_id', true);
        $this->addForeignKey('geo_area_geo_region_region_id_fk', 'geo_area', 'region_id',
            'geo_region', 'region_id', 'NO ACTION', 'CASCADE');
        
        $this->insertData();
    }
    
    public function insertData()
    {
        $region_query = GeoRegion::find()->select(['region_id', 'region_name'])
            ->where(['geo_id' => 228])->asArray()->all();
    
        $regions = [];
        foreach ($region_query as $k => $v) {
            if ($v['region_id'] == 6) {
                $regions['Ras Al Khaima'] = 6;
            } else {
                $regions[$v['region_name']] = $v['region_id'];
            }
        }
        $areas = array_map('str_getcsv', file(__DIR__ . '/documents/area.csv'));
    
        foreach ($areas as $raw) {
            if (isset($regions[$raw[0]])) {
                $geo_area = new GeoArea();
                $geo_area->region_id = $regions[$raw[0]];
                $geo_area->main_area = $raw[1];
                $geo_area->sub_area = $raw[2];
                $geo_area->area_known_locally = $raw[3];
                $geo_area->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('geo_area_geo_region_region_id_fk', 'geo_area');
        $this->dropIndex('geo_area_area_id_uindex', 'geo_area');
    
        $this->dropTable('{{%geo_area}}');
    }
}
