<?php

use yii\db\Migration;

/**
 * Handles adding send_to_lp_crm and auto_send_to_lp_crm to table `target_advert_group`.
 */
class m190722_141703_add_send_to_lp_crm_columns_to_target_advert_group_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('target_advert_group', 'send_to_lp_crm', $this->smallInteger(1)->defaultValue(0));
        $this->addColumn('target_advert_group', 'auto_send_to_lp_crm', $this->smallInteger(1)->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('target_advert_group', 'send_to_lp_crm');
        $this->dropColumn('target_advert_group', 'auto_send_to_lp_crm');
    }
}
