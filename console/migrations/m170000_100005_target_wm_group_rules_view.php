<?php

use yii\db\Migration;
use yii\db\Schema;

class m170000_100005_target_wm_group_rules_view extends Migration
{
    public function up()
    {
        $select_rows = '
        WOT.offer_id,
        TWG.target_wm_group_id,
        TWG.use_commission_rules,
        TWG.base_commission,
        TWG.exceeded_commission,
        TWGR.rule_id,
        TWGR.amount,
        TWGR.commission,
        TWG.active
         ';
        Yii::$app->db->createCommand('CREATE VIEW target_wm_rules_view AS
        SELECT ' . $select_rows . ' FROM target_wm_group TWG
JOIN wm_offer_target WOT ON WOT.wm_offer_target_id = TWG.wm_offer_target_id
JOIN target_wm_group_rules TWGR ON TWGR.target_wm_group_id = TWG.target_wm_group_id
ORDER BY WOT.offer_id, TWG.target_wm_group_id, TWGR.amount
')->execute();
    }

    public function safeDown()
    {
        Yii::$app->db->createCommand('DROP VIEW target_wm_rules_view')->execute();
    }
}
