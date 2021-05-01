<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100007_order_view extends Migration
{
    public function up()
    {
        $select_rows = '
        O.order_id, 
        O.order_hash, 
        O.order_status, 
        O.status_reason, 
        O.created_at, 
        O.delivery_date, 
        O.total_amount, 
        O.total_cost, 
        O.advert_commission, 
        O.wm_commission, 
        O.usd_total_cost,
        O.usd_advert_commission, 
        O.usd_wm_commission, 
        U.username as owner_name, 
        TA.advert_id as owner_id, 
        O.target_advert_id, 
        O.target_wm_id, 
        O.customer_id, 
        C.name as customer_name, 
        AOT.advert_offer_target_id, 
        AOT.advert_offer_target_status, 
        AOT.geo_id as country_id, 
        G.iso, 
        O.bitrix_flag, 
        
        CUR.currency_id as advert_currency_id,  
        CUR.currency_name as advert_currency_name,
        
        CURR.currency_id,  
        CURR.currency_name,  
        
        G.geo_name as country_name, 
        O.offer_id, 
        OF.offer_name, 
        OD.declaration,
        OD.referrer,
        OD.sub_id_1,
        OD.sub_id_2,
        OD.sub_id_3,
        OD.sub_id_4,
        OD.sub_id_5,
        OD.view_time,
        OD.view_hash,
        F.flow_id,
        OD.wm_id,
        F.wm_id as flow_wm_id,
        Us.username as flow_wm_name, 
        Usr.username as wm_name,
        O.deleted,
        
        O.paid_online,
        OP.amount as payment_amount,
        OP.payment_status,
        OP.message as payment_message,
        
        ODL.delivery_api_id, 
        ODL.user_api_id, 
        ODL.delivery_api_name, 
        ODL.sent_by, 
        ODL.tracking_no, 
        ODL.shipment_no,
        ODL.remote_status,
        ODL.report_no,
        ODL.delivery_date_in_fact,
        ODL.money_in_fact,
        
        O.comment,
        O.information
         ';
        Yii::$app->db->createCommand('
        CREATE VIEW order_view AS 
        SELECT ' . $select_rows . ' FROM `order` O 
        LEFT JOIN `offer` OF ON OF.offer_id = O.offer_id 
        LEFT JOIN `customer` C ON C.customer_id = O.customer_id 
        LEFT JOIN `target_advert` TA ON TA.target_advert_id = O.target_advert_id 
        LEFT JOIN `target_advert_group` TAG ON TAG.target_advert_group_id = TA.target_advert_group_id 
        LEFT JOIN `advert_offer_target` AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id 
        LEFT JOIN `geo` G ON G.geo_id = AOT.geo_id 
        
        LEFT JOIN `currency` CUR ON CUR.currency_id = TAG.currency_id 
        LEFT JOIN `currency` CURR ON CURR.country_id = C.country_id 
        
        LEFT JOIN `order_data` OD ON OD.order_id = O.order_id 
        LEFT JOIN `flow` F ON F.flow_id = O.flow_id 
        LEFT JOIN `user` U ON U.id = TA.advert_id
        LEFT JOIN `user` Us ON Us.id = F.wm_id
        LEFT JOIN `user` Usr ON Usr.id = OD.wm_id
        LEFT JOIN `order_delivery` ODL ON ODL.order_id = O.order_id
        LEFT JOIN `online_payment` OP ON OP.order_id = O.order_id
        ')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW order_view')->execute();
    }
}
