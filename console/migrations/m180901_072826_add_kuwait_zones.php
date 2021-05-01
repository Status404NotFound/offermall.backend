<?php

use yii\db\Migration;

/**
 * Class m180901_072826_add_kuwait_zones
 */
class m180901_072826_add_kuwait_zones extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insertData();
    }

    public function insertData()
    {
        $array = ['Al Fintas', 'As Salimiyah', 'Ardiyah', 'Al Fahahil',
            'Ar Rumaythiyah', 'Ar Riqqah', 'Salwa', 'Mangaf', 'Ar Rabiyah', 'Bayan', 'Janub as Surrah', 'Ad Dasmah',
            'Ash Shamiyah', 'Al Funaytis', 'Mubarak al Kabir', 'Doha', 'Sulaibikhat',
            'Al Mahbulah', 'Sulaibiya', 'Az Zawr', 'Fahaheel', 'Mina Abd Allah', 'Ali Sabah Al Salem', 'Sabah Al Ahmad',
            'Jeleeb Al Shuyoukh'];

        foreach ($array as $item) {
            $this->insert('geo_region', ['region_name' => $item, 'geo_id' => '118']);
            $this->insert('geo_city', ['city_name' => $item, 'region_id' => '', 'geo_id' => '118']);
        }
    }
}
