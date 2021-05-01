<?php

use yii\db\Migration;

class m171018_103939_user_permission extends Migration
{
    public function safeUp()
    {
        $this->createTable('user_permission', [
            'user_permission_id' => $this->primaryKey(),
            'user_id' => $this->integer(4)->notNull(),
            'permission_id' => $this->integer(4)->notNull(),
            'is_active' => $this->boolean()->notNull(),
        ]);

        $this->createIndex('user_permission_Idx_user_id_permission_id', 'user_permission', ['user_id', 'permission_id'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('user_permission_Idx_user_id_permission_id', 'user_permission');
        $this->dropTable('user_permission');
    }

}
