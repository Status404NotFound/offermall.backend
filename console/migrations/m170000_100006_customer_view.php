<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100006_customer_view extends Migration
{
    public function up()
    {
        //CONCAT (C.phone_country_code, "-", C.phone) AS phone,
//        Yii::$app->db->createCommand('DROP VIEW IF EXISTS customer_view')->execute();

        $select_rows = '
        C.customer_id, 
        C.name, 
        C.phone, 
        C.email, 
        C.customer_status, 
        C.description, 
        CS.ip, 
        CS.os, 
        CS.browser, 
        CS.sid, 
        CS.view_hash, 
        CS.cookie, 
        CN.id as country_id, 
        CN.country_name,
        CN.country_code,
        GR.region_id, 
        GR.region_name, 
        GC.city_id, 
        GC.city_name, 
        C.address,
        C.pin 
         ';
        Yii::$app->db->createCommand('CREATE VIEW customer_view AS 
        SELECT ' . $select_rows . ' FROM `customer` C 
        LEFT JOIN `customer_system` CS ON CS.customer_id = C.customer_id 
        LEFT JOIN `countries` CN ON CN.id = C.country_id 
        LEFT JOIN `geo_city` GC ON GC.city_id = C.city_id 
        LEFT JOIN `geo_region` GR ON GR.region_id = C.region_id 
        ')
            ->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW customer_view')->execute();
    }
}
