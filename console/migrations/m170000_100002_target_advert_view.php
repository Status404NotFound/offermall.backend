<?php

use yii\db\Migration;

class m170000_100002_target_advert_view extends Migration
{
    public function up()
    {
        $select_rows = '
        AOT.offer_id,
        AOT.advert_offer_target_id,
        TA.target_advert_group_id,
        TA.target_advert_id,
        AOT.advert_offer_target_status,
        AOT.geo_id,
        G.geo_name,
        G.iso as geo_iso,
        TA.advert_id,
        U.username as advert_name,
        TA.stock_id,
        TAG.daily_limit,
        TAG.currency_id,
        C.currency_name,
        TAG.base_commission,
        TAG.exceeded_commission,
        TAG.use_commission_rules,
        AOT.wm_visible,
        TAG.send_sms_customer,
        TAG.send_second_sms_customer,
        TAG.send_sms_owner,
        TAG.sms_text_customer,
        TAG.second_sms_text_customer,
        TAG.sms_text_owner,
        AOT.active as aot_active,
        TAG.active as tag_active,
        TA.active as ta_active 
         ';
        Yii::$app->db->createCommand('CREATE VIEW target_advert_view AS 
        SELECT ' . $select_rows . ' FROM target_advert TA 
JOIN target_advert_group TAG ON TAG.target_advert_group_id = TA.target_advert_group_id 
JOIN advert_offer_target AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id 
JOIN geo G ON G.geo_id = AOT.geo_id 
JOIN user U ON U.id = TA.advert_id 
JOIN currency C ON C.currency_id = TAG.currency_id
ORDER BY AOT.offer_id, TAG.target_advert_group_id, TA.advert_id
')
            ->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW target_advert_view')->execute();
    }
}
