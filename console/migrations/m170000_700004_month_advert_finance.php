<?php

use yii\db\Migration;

class m170000_700004_month_advert_finance extends Migration
{

    public function safeUp()
    {
//        Yii::$app->db->createCommand('CREATE PROCEDURE advert_finance_paginate(IN AdvertId INT, IN FirstRow INT, IN PageRows INT)
//  BEGIN
//    SELECT
//      U.id                AS user_id,
//      U.username          AS user_name,
//      MONTHNAME(O.created_at)  AS date,
//      count(O.order_id)   AS leads_count,
//      sum(O.total_amount) AS orders_sku_total_amount,
//      sum(O.total_cost)   AS orders_total_cost,
//      (SELECT count(*) FROM `order` ,  `order_data` WHERE  `order_data`.order_id = `order`.order_id AND `order`.order_status = 100 AND  MONTHNAME(`order`.created_at)  = date) AS success_delivery
//
//    FROM `order` O
//      JOIN `target_advert` TA ON TA.target_advert_id = O.target_advert_id
//      JOIN `target_advert_group` TAG ON TAG.target_advert_group_id = TA.target_advert_group_id
//      JOIN `advert_offer_target` AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id
//      JOIN `user` U ON U.id = 5
//
//    WHERE O.order_status >= AOT.advert_offer_target_status
//          AND TA.advert_id = 5
//
//    GROUP BY date
//			ORDER BY date DESC
//    ORDER BY DATE DESC
//    LIMIT PageRows OFFSET FirstRow;
//  END;')->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP PROCEDURE IF EXISTS advert_finance')->execute();
    }
}
