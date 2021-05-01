<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100003_target_wm_view extends Migration
{
    public function up()
    {
        $select_rows = '
        WOT.offer_id,
        TWG.target_wm_group_id,
        TWG.wm_offer_target_id,
        WOT.advert_offer_target_status,
        WOT.wm_offer_target_status,
        WOT.geo_id,
        G.geo_name,
        TW.wm_id,
        U.username as wm_name,
        TWG.base_commission,
        TWG.exceeded_commission,
        TWG.use_commission_rules,
        TWG.hold,
        WOT.active as wot_active,
        TWG.active as twm_active,
        TWG.view_for_all,
        TW.excepted
         ';
        Yii::$app->db->createCommand('CREATE VIEW target_wm_view AS
        SELECT ' . $select_rows . ' FROM target_wm_group TWG
        JOIN wm_offer_target WOT ON WOT.wm_offer_target_id = TWG.wm_offer_target_id
LEFT JOIN target_wm TW ON TW.target_wm_group_id = TWG.target_wm_group_id
JOIN geo G ON G.geo_id = WOT.geo_id
LEFT JOIN user U ON U.id = TW.wm_id
ORDER BY WOT.offer_id, TW.wm_id
')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW target_wm_view')->execute();
    }
}
