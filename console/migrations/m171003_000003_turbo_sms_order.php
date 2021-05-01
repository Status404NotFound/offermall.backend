<?php

use yii\db\Migration;

/**
 * Class m171222_105551_turbo_sms_order
 */
class m171003_000003_turbo_sms_order extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%turbo_sms_order}}', [
            'id' => $this->primaryKey(11),
            'order_id' => $this->integer(11)->notNull(),
            'turbo_sms_id' => $this->integer(11)->notNull(),
            'sms_status' => $this->smallInteger(6)->null(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);
        $this->addForeignKey('turbo_sms_order_fk_sms_id', 'turbo_sms_order', 'turbo_sms_id', 'turbo_sms_sent', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('turbo_sms_order_fk_order_id', 'turbo_sms_order', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('turbo_sms_order_fk_sms_id', 'turbo_sms_order');
        $this->dropForeignKey('turbo_sms_order_fk_order_id', 'turbo_sms_order');
        $this->dropTable('{{%turbo_sms_order}}');
    }
}
