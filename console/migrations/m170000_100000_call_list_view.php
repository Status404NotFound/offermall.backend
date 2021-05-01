<?php

use yii\db\Migration;

class m170000_100000_call_list_view extends Migration
{
    public function up()
    {
        $select_rows = '
        CL.order_id, 
        CL.time_to_call,
        CL.attempts,
        CL.lead_status,
        CL.lead_state,
        CL.language_id,
        CL.operator_id,
        CL.updated_at as call_list_updated_at,
        
        O.order_hash, 
        O.customer_id,
        O.order_status, 
        O.created_at, 
        O.delivery_date, 
        O.total_amount, 
        O.total_cost, 
        O.advert_commission, 
        O.target_advert_id,
        O.offer_id, 
        O.paid_online as paid_online
        
        U.username as owner_name, 
        TA.advert_id as owner_id, 
        
        U1.username as operator_name,
         
        C.name as customer_name, 
        C.phone,
        C.phone_country_code,
        C.phone_extension,
        C.country_id,
        C.city_id,
        C.address,
        C.email,
        
        G.geo_name as country_name,
        
        GC.city_name,
        
        OF.offer_name, 
        
        OO.is_active as is_enable_offer,
        OO.user_id as offer_operator_id,
        
        OL.user_id as language_operator_id,
        OL.is_active as is_enable_language,
        
        OG.user_id as geo_operator_id,
        OG.is_active as is_enable_geo,
     
        L.name as language_name,
        L.code as language_code
        
         ';
        Yii::$app->db->createCommand('CREATE VIEW call_list_view AS 
        SELECT ' . $select_rows . ' FROM `call_list` CL  
        LEFT JOIN `order` O on O.order_id = CL.order_id
        LEFT JOIN `offer` OF ON OF.offer_id = O.offer_id 
        LEFT JOIN `customer` C ON C.customer_id = O.customer_id 
        LEFT JOIN `geo` G on G.geo_id = C.country_id
        LEFT JOIN `geo_city` GC on GC.city_id = C.city_id
        LEFT JOIN `target_advert` TA ON TA.target_advert_id = O.target_advert_id 
        LEFT JOIN `user` U ON U.id = TA.advert_id
        LEFT JOIN `user` U1 on U1.id = CL.operator_id
        
        LEFT JOIN `operator_offer` OO ON OO.offer_id = O.offer_id 
        LEFT JOIN `operator_language` OL on OL.language_id = CL.language_id
        LEFT JOIN `operator_geo` OG ON OG.country_id = C.country_id
        
        LEFT JOIN `language` L on L.language_id = CL.language_id
        
        WHERE O.order_status = '.\common\models\order\OrderStatus::PENDING.'
        or O.order_status = '.\common\models\order\OrderStatus::BACK_TO_PENDING.'
        ')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW call_list_view')->execute();
    }
}
