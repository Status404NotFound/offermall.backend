<?php

use yii\db\Migration;

/**
 * Class m200000_100006_add_fields_target_advert_group
 */
class m120000_100006_add_fields_target_advert_group extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->addColumn('target_advert_group', 'send_second_sms_customer', 'smallint(1) AFTER send_sms_customer');
        $this->addColumn('target_advert_group', 'second_sms_text_customer', 'varchar(255) AFTER sms_text_customer');
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        $this->dropColumn('target_advert_group', 'send_second_sms_customer');
        $this->dropColumn('target_advert_group', 'second_sms_text_customer');
    }
}
