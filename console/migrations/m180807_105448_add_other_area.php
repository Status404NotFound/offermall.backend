<?php

use yii\db\Migration;

/**
 * Class m180807_105448_add_other_area
 */
class m180807_105448_add_other_area extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 2]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 3]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 4]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 5]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 6]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 7]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 8]);
        $this->insert('geo_area', ['main_area' => '', 'sub_area' => 'Other', 'area_known_locally' => '','region_id' => 9]);
    }
}