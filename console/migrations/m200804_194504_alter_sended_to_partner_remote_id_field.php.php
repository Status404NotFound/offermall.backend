<?php

use yii\db\Migration;

/**
 * Class m200804_194504_alter_sended_to_partner_remote_id_field
 */
class m200804_194504_alter_sended_to_partner_remote_id_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('sended_to_partner', 'remote_order_id', $this->string(255)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn('sended_to_partner', 'remote_order_id', $this->integer(11)->notNull());
    }
}
