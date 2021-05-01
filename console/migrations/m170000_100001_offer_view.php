<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100001_offer_view extends Migration
{
    public function up()
    {
        Yii::$app->db->createCommand('DROP VIEW IF EXISTS offer_view')->execute();
        $select_rows = '
        O.offer_id,
        O.offer_name,
        O.offer_status,
        O.offer_hash,
        AOT.geo_id,
        G.geo_name,
        G.iso as geo_iso,
        TA.target_advert_id,
        TA.advert_id,
        TA.active,
        U.username as advert_name,      
        TAG.target_advert_group_id,
        AOT.advert_offer_target_id,
        TAG.send_sms_customer,
        TAG.send_sms_owner,
        TAG.sms_text_customer,
        TAG.sms_text_owner,

        O.description,
        O.img
         ';
        Yii::$app->db->createCommand('CREATE VIEW offer_view AS 
        SELECT ' . $select_rows . ' FROM offer O 
        LEFT JOIN advert_offer_target AOT ON AOT.offer_id = O.offer_id 
        LEFT JOIN target_advert_group TAG ON TAG.advert_offer_target_id = AOT.advert_offer_target_id 
        LEFT JOIN target_advert TA ON TA.target_advert_group_id = TAG.target_advert_group_id 
        LEFT JOIN user U ON U.id = TA.advert_id 
        LEFT JOIN geo G ON G.geo_id = AOT.geo_id 
ORDER BY O.offer_id, AOT.geo_id, TA.advert_id 
 ')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW offer_view')->execute();
    }
}
