<?php

use yii\db\Migration;

class m170000_700002_advert_finance_month_balance_paginate extends Migration
{
    public function safeUp()
    {
        Yii::$app->db->createCommand('CREATE PROCEDURE advert_finance_month_balance_paginate(IN AdvertId INT, IN FirstRow INT, IN PageRows INT)
  BEGIN
      SELECT
      DATE_FORMAT(O.created_at, "%m.%Y") as date,
      -SUM(O.total_cost)  AS total_down,
     (SELECT SUM(sum) as sum FROM advert_money_entrance WHERE DATE_FORMAT(entrance_date, "%m.%Y") = date) AS total_up,

      AM.currency_id,
      CUR.currency_name AS currency
    FROM `order` O
      JOIN `target_advert` TA ON TA.target_advert_id = O.target_advert_id
      JOIN `target_advert_group` TAG ON TAG.target_advert_group_id = TA.target_advert_group_id
      JOIN `advert_offer_target` AOT ON AOT.advert_offer_target_id = TAG.advert_offer_target_id
      JOIN `advert_money` AM ON AM.advert_id = AdvertId
      JOIN `currency` CUR ON CUR.currency_id = AM.currency_id

    WHERE O.order_status >= AOT.advert_offer_target_status
          AND TA.advert_id = AdvertId

    GROUP BY date, currency_id
    ORDER BY date DESC
    LIMIT PageRows OFFSET FirstRow;
  END;')->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP PROCEDURE IF EXISTS advert_finance_month_balance_paginate')->execute();
    }
}
