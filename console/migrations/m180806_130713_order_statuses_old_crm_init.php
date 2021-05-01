<?php

use yii\db\Migration;

/**
 * Class m180806_130713_order_statuses_old_crm_init
 */
class m180806_130713_order_statuses_old_crm_init extends Migration
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
        $this->createTable('order_statuses_old_crm', [
            'id' => $this->primaryKey(),
            'status_id' => $this->smallInteger(3)->notNull(),
            'status_name' => $this->string(255)->notNull(),
            'is_valid' => $this->smallInteger(3)->defaultValue('1')->notNull(),
            'has_reason' => $this->smallInteger(3)->defaultValue('0')->notNull(),
        ], $tableOptions);
        $this->insertData();
    }

    /**
     * @inheritdoc
     */
    public function insertData()
    {
        $this->insert('order_statuses_old_crm', ['status_id' => '0', 'status_name' => 'PENDING']);
        $this->insert('order_statuses_old_crm', ['status_id' => '1', 'status_name' => 'APPROVED']);
        $this->insert('order_statuses_old_crm', ['status_id' => '2', 'status_name' => 'REJECTED']);
        $this->insert('order_statuses_old_crm', ['status_id' => '10', 'status_name' => 'WAITING DELIVERY']);
        $this->insert('order_statuses_old_crm', ['status_id' => '11', 'status_name' => 'DELIVERY IN PROGRESS']);
        $this->insert('order_statuses_old_crm', ['status_id' => '12', 'status_name' => 'CANCELED']);
        $this->insert('order_statuses_old_crm', ['status_id' => '111', 'status_name' => 'SUCCESS DELIVERY']);
        $this->insert('order_statuses_old_crm', ['status_id' => '110', 'status_name' => 'NOT PAID']);
        $this->insert('order_statuses_old_crm', ['status_id' => '112', 'status_name' => 'RETURNED']);
        $this->insert('order_statuses_old_crm', ['status_id' => '3', 'status_name' => 'NOT VALID']);
        $this->insert('order_statuses_old_crm', ['status_id' => '30', 'status_name' => 'NOT VALID CHECKED']);
        $this->insert('order_statuses_old_crm', ['status_id' => '31', 'status_name' => 'VALID']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('order_statuses_old_crm');
    }

}
