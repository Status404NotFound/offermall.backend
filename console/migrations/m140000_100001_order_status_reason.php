<?php

use yii\db\Migration;

/**
 * Class m400000_100001_order_status_reason
 */
class m140000_100001_order_status_reason extends Migration
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
        $this->createTable('order_status_reason', [
            'id' => $this->primaryKey(),
            'reason_id' => $this->smallInteger(3)->notNull(),
            'reason_name' => $this->string(255)->notNull(),
            'is_valid' => $this->smallInteger(3)->notNull()->defaultValue(1),
        ], $tableOptions);

        $this->batchInsert('order_status_reason', ['reason_id', 'reason_name', 'is_valid'],
            [
                ['reason_id' => 0, 'reason_name' => 'Rejected by Admin', 'is_valid' => 0],
                ['reason_id' => 1, 'reason_name' => 'Customer is out of country', 'is_valid' => 1],
                ['reason_id' => 2, 'reason_name' => 'Error in the phone number', 'is_valid' => 0],
                ['reason_id' => 3, 'reason_name' => 'I did not order', 'is_valid' => 1],
                ['reason_id' => 4, 'reason_name' => 'Duplicate order', 'is_valid' => 0],
                ['reason_id' => 5, 'reason_name' => 'Too expensive', 'is_valid' => 1],
                ['reason_id' => 6, 'reason_name' => 'Ordered elsewhere', 'is_valid' => 1],
                ['reason_id' => 7, 'reason_name' => 'Order to mistress but wife took the phone', 'is_valid' => 1],
                ['reason_id' => 8, 'reason_name' => 'A subscriber can not receive the call at the moment', 'is_valid' => 0],
                ['reason_id' => 9, 'reason_name' => 'Undefined language', 'is_valid' => 1],
                ['reason_id' => 10, 'reason_name' => 'Product is out of stock', 'is_valid' => 0],
                ['reason_id' => 11, 'reason_name' => 'Customer will order late', 'is_valid' => 1],
                ['reason_id' => 12, 'reason_name' => 'Not Supported Region', 'is_valid' => 0],
                ['reason_id' => 13, 'reason_name' => 'Test Conversion or Offer', 'is_valid' => 0],
                ['reason_id' => 14, 'reason_name' => 'Unhappy with delivery charge', 'is_valid' => 1],
                ['reason_id' => 15, 'reason_name' => 'Children\'s joke', 'is_valid' => 1],
                ['reason_id' => 16, 'reason_name' => 'Consultation', 'is_valid' => 1],
                ['reason_id' => 17, 'reason_name' => 'Customer is not interested anymore', 'is_valid' => 1],
                ['reason_id' => 18, 'reason_name' => 'Weird details', 'is_valid' => 0],
                ['reason_id' => 19, 'reason_name' => 'The subscriber is out of network coverage', 'is_valid' => 0],
                ['reason_id' => 20, 'reason_name' => 'Wrong Geo', 'is_valid' => 0],
                ['reason_id' => 21, 'reason_name' => 'No WM', 'is_valid' => 0],
                ['reason_id' => 22, 'reason_name' => 'Not correct details', 'is_valid' => 0],
                ['reason_id' => 23, 'reason_name' => 'NA for 4 days', 'is_valid' => 0],
                ['reason_id' => 24, 'reason_name' => 'Low quality', 'is_valid' => 1],
                ['reason_id' => 25, 'reason_name' => 'Out of money', 'is_valid' => 1],
                ['reason_id' => 26, 'reason_name' => 'Black list', 'is_valid' => 0]
            ]);

        $this->createIndex('idx-order_status_reason_id', 'order_status_reason', 'reason_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-order_status_reason_id', 'order_status_reason');
        $this->dropTable('order_status_reason');
    }
}
