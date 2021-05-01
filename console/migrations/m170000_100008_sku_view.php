<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100008_sku_view extends Migration
{
    public function up()
    {
        $select_rows = 'P.product_id, product_name, sku_id,  sku_name, sku_alias, color, geo_id, advert_id ';
        $from_tables = 'product P, product_sku PS ';
        Yii::$app->db->createCommand('CREATE VIEW sku_view AS 
        SELECT ' . $select_rows . ' FROM ' . $from_tables . ' 
        WHERE P.product_id = PS.product_id')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW sku_view')->execute();
    }
}
