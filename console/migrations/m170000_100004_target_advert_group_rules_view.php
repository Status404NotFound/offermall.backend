<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100004_target_advert_group_rules_view extends Migration
{
    public function up()
    {
        $select_rows = '
        AOT.offer_id,
        TAG.target_advert_group_id,
        TA.target_advert_id,
        TA.advert_id,
        U.username as advert_name,
        TAG.daily_limit,
        TAG.use_commission_rules,
        TAG.base_commission,
        TAG.exceeded_commission,
        TAGR.rule_id,
        TAGR.amount,
        TAGR.commission,
        TA.stock_id,
        TAG.currency_id,
        C.currency_name,
        TAG.send_sms_customer,
        TAG.send_sms_owner,
        TAG.sms_text_customer,
        TAG.sms_text_owner,
        TAG.active as tag_active,
        TA.active as ta_active
         ';
        Yii::$app->db->createCommand('CREATE VIEW target_advert_group_rules_view AS 
        SELECT ' . $select_rows . ' FROM target_advert_group TAG
JOIN target_advert TA ON TA.target_advert_group_id = TAG.target_advert_group_id
JOIN user U ON U.id = TA.advert_id
JOIN advert_offer_target AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id
JOIN currency C ON C.currency_id = TAG.currency_id
JOIN target_advert_group_rules TAGR ON TAGR.target_advert_group_id = TA.target_advert_group_id
ORDER BY AOT.offer_id, TA.target_advert_group_id, TA.advert_id
')
            ->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW target_advert_group_rules_view')->execute();
    }
}
