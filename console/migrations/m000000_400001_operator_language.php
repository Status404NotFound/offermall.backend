<?php

use yii\db\Migration;

class m000000_400001_operator_language extends Migration
{
    public function safeUp()
    {
        $this->createTable('operator_language', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(4),
            'language_id' => $this->integer(4),
            'is_active' => $this->boolean(),
        ]);

        $this->createIndex('index_unique_userid_and_lgid', 'operator_language', ['user_id', 'language_id'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('index_unique_userid_and_lgid', 'operator_language');
        $this->dropTable('operator_language');
    }
}
