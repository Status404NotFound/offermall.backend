<?php

use yii\db\Migration;

class m700000_200000_finance_all extends Migration
{
    public function safeUp()
    {
        $select_rows = "
        DATE(O.created_at) AS date,
        O.offer_id,
        OF.offer_name,
        count(O.order_id) as leads_count,
        sum(O.total_amount) as count,
        sum(O.total_cost) as total ";

        Yii::$app->db->createCommand('CREATE VIEW finance AS 
        SELECT ' . $select_rows . ' FROM `order` O 
JOIN `offer` OF ON OF.offer_id = O.offer_id 
JOIN `target_advert` TA ON TA.target_advert_id = O.target_advert_id 
JOIN `target_advert_group` TAG ON TAG.target_advert_group_id = TA.target_advert_group_id 
JOIN advert_offer_target AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id 
WHERE O.order_status >= AOT.advert_offer_target_status 
GROUP BY  date, offer_id
        ')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW finance_all')->execute();
    }

}
