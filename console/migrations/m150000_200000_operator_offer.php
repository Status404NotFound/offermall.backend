<?php

use yii\db\Migration;

class m150000_200000_operator_offer extends Migration
{
    public function up()
    {
        $this->createTable('operator_offer', [
            'id' => $this->primaryKey(),
            'offer_id' => $this->integer(4)->notNull(),
            'user_id' => $this->integer(4)->notNull(),
            'is_active' => $this->boolean()->defaultValue(1),
        ]);

        $this->createIndex('offer-user-index-user_id', 'operator_offer', ['offer_id', 'user_id'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('offer-user-index-user_id', 'operator_offer');
        $this->dropTable('operator_offer');
    }
}
