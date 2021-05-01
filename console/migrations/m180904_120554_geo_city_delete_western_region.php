<?php

use yii\db\Migration;

/**
 * Class m180904_120554_geo_city_delete_western_region
 */
class m180904_120554_geo_city_delete_western_region extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->db->createCommand('UPDATE customer SET city_id = 2 WHERE city_id = 8')->execute();
        \common\models\geo\GeoCity::deleteAll(['city_id' => 8]);
        \common\models\geo\GeoRegion::deleteAll(['region_id' => 8]);
    }
}
