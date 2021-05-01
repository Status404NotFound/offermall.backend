<?php

use yii\db\Migration;

/**
 * Class m180507_145805_addEgyptRegion
 */
class m180507_145805_addEgyptRegion extends Migration
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
//        $query = file_get_contents(__DIR__ . '/sql/geo_region.sql');
//        Yii::$app->db->createCommand($query)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180507_145805_addEgyptRegion cannot be reverted.\n";

        return false;
    }
}
