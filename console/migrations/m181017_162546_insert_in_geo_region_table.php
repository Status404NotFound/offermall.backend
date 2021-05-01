<?php

use yii\db\Migration;

/**
 * Class m181017_162546_insert_in_geo_region_table
 */
class m181017_162546_insert_in_geo_region_table extends Migration
{
    const COUNTRY_ID_NIGERIA = 161;
    
    /**
     * @var string Table name
     */
    protected $table = 'geo_region';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert(
            $this->table,
            [
                'region_name' => 'Port Harcourt',
                'geo_id' => self::COUNTRY_ID_NIGERIA,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete(
            $this->table,
            [
                'region_name' => 'Port Harcourt',
                'geo_id' => self::COUNTRY_ID_NIGERIA,
            ]
        );
    }
}
