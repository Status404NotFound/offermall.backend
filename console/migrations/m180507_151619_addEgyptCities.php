<?php

use yii\db\Migration;

/**
 * Class m180507_151619_addEgyptCities
 */
class m180507_151619_addEgyptCities extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insertData();
    }

    public function insertData()
    {
//        $query = file_get_contents(__DIR__ . '/sql/geo_city.sql');
//        Yii::$app->db->createCommand($query)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m180507_151619_addEgyptCities cannot be reverted.\n";
//
//        return false;
    }
}
