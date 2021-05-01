<?php

use yii\db\Migration;

/**
 * Class m181018_102740_insert_in_geo_city_table
 */
class m181018_102740_insert_in_geo_city_table extends Migration
{
    const COUNTRY_ID_NIGERIA = 161;

    /**
     * @var string Table name
     */
    protected $table = 'geo_city';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert(
            $this->table,
            [
                'city_name' => 'Port Harcourt',
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
                'city_name' => 'Port Harcourt',
                'geo_id' => self::COUNTRY_ID_NIGERIA,
            ]
        );
    }
}
