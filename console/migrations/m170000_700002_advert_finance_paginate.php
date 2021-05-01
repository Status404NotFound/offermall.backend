<?php

use yii\db\Migration;

class m170000_700002_advert_finance_paginate extends Migration
{
    public function safeUp()
    {
//        Yii::$app->db->createCommand('CREATE PROCEDURE advert_finance_paginate(IN AdvertId INT, IN FirstRow INT, IN PageRows INT)
//  BEGIN
//    SELECT
//      U.id                AS user_id,
//      U.username          AS user_name,
//      DATE(O.created_at)  AS date,
//      O.offer_id,
//      OF.offer_name,
//      count(O.order_id)   AS leads_count,
//      sum(O.total_amount) AS orders_sku_total_amount,
//      sum(O.total_cost)   AS orders_total_cost,
//      TAG.currency_id,
//      CUR.currency_name
//    FROM `order` O
//      JOIN `offer` OF ON OF.offer_id = O.offer_id
//      JOIN `target_advert` TA ON TA.target_advert_id = O.target_advert_id
//      JOIN `target_advert_group` TAG ON TAG.target_advert_group_id = TA.target_advert_group_id
//      JOIN `advert_offer_target` AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id
//      JOIN `user` U ON U.id = AdvertId
//      JOIN `advert_money` AM ON AM.advert_id = AdvertId
//      JOIN `currency` CUR ON CUR.currency_id = AM.currency_id
//
//    WHERE O.order_status >= AOT.advert_offer_target_status
//          AND TA.advert_id = AdvertId
//
//    GROUP BY DATE, offer_id, currency_id
//    ORDER BY DATE DESC
//    LIMIT PageRows OFFSET FirstRow;
//  END;')->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        Yii::$app->db->createCommand('DROP PROCEDURE IF EXISTS advert_finance_paginate')->execute();
    }
}
