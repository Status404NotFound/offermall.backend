<?php

use yii\db\Migration;

/**
 * Class m600000_400013_customer_blacklist
 */
class m160000_400013_customer_blacklist extends Migration
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
        $this->createTable('customer_blacklist', [
            'customer_black_list_id'=>$this->primaryKey(),
            'ip' => $this->string(255),
            'phone' => $this->string(255),
            'email' => $this->string(255),
            'reason_id' => $this->integer(11)->notNull(),
            'status_id' => $this->integer(11)->notNull(),
            'is_active' => $this->smallInteger(2)->defaultValue(1),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11),
        ], $tableOptions);

        $this->createIndex('ip_customer_blacklist', 'customer_blacklist', 'ip');
        $this->createIndex('phone_customer_blacklist', 'customer_blacklist', 'phone');
        $this->createIndex('email_customer_blacklist', 'customer_blacklist', 'email');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('ip_customer_blacklist', 'customer_blacklist');
        $this->dropIndex('phone_customer_blacklist', 'customer_blacklist');
        $this->dropIndex('email_customer_blacklist', 'customer_blacklist');

        $this->dropTable('customer_blacklist');
    }
}
