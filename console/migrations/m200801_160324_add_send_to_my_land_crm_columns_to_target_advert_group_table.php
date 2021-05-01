<?php

use yii\db\Migration;

/**
 * Handles adding send_to_lp_crm and auto_send_to_lp_crm to table `target_advert_group`.
 */
class m200801_160324_add_send_to_my_land_crm_columns_to_target_advert_group_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('target_advert_group', 'send_to_my_land_crm', $this->smallInteger(1)->defaultValue(0));
        $this->addColumn('target_advert_group', 'auto_send_to_my_land_crm', $this->smallInteger(1)->defaultValue(0));

        $this->insert('partner_crm', [
            'name' => 'My Land Trading CRM',
            'slug' => 'my_land_crm'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->delete('partner_crm', ['slug' => 'my_land_crm']);
        $this->dropColumn('target_advert_group', 'send_to_my_land_crm');
        $this->dropColumn('target_advert_group', 'auto_send_to_my_land_crm');
    }
}
