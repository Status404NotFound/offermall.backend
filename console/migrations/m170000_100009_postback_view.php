<?php

use yii\db\Migration;

/**
 * Class m700000_100009_postback_view
 */
class m170000_100009_postback_view extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function up()
    {
        $select_rows = '
        O.order_id,
        O.order_hash,
        O.order_status,
        WOT.wm_offer_target_status,
        O.wm_commission,
        G.geo_name as country_name,
        OF.offer_name,
        TW.active AS t_wm_active,
        TWG.active AS t_wm_group_active,
        OD.referrer,
        OD.sub_id_1,
        OD.sub_id_2,
        OD.sub_id_3,
        OD.sub_id_4,
        OD.sub_id_5,
        OD.view_time,
        OD.view_hash,
        CYS.browser,
        CYS.os,
        CYS.ip,
        F.flow_key,
        F.flow_id,
        F.wm_id as wm_id,
        Us.username as wm_name
         ';
        Yii::$app->db->createCommand('CREATE VIEW postback_view AS 
        SELECT ' . $select_rows . ' FROM `order` O 
        LEFT JOIN `offer` OF ON OF.offer_id = O.offer_id 
        LEFT JOIN `customer` C ON C.customer_id = O.customer_id 
        LEFT JOIN `customer_system` CYS ON C.customer_id = CYS.customer_id 
        LEFT JOIN `target_wm` TW ON TW.target_wm_id = O.target_wm_id 
        LEFT JOIN `target_wm_group` TWG ON TWG.target_wm_group_id = TW.target_wm_group_id 
        LEFT JOIN `wm_offer_target` WOT ON WOT.wm_offer_target_id = TWG.wm_offer_target_id 
        LEFT JOIN `geo` G ON G.geo_id = WOT.geo_id 
        LEFT JOIN `order_data` OD ON OD.order_id = O.order_id 
        LEFT JOIN `flow` F ON F.flow_id = O.flow_id 
        LEFT JOIN `user` U ON U.id = TW.wm_id
        LEFT JOIN `user` Us ON Us.id = F.wm_id
        ')->execute();
    }

    /**
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW postback_view')->execute();
    }
}
