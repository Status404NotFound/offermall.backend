<?php

use yii\db\Migration;

class m170921_102851_role_permission extends Migration
{
    public function safeUp()
    {
        $this->createTable('role_permission',[
            'role_permission_id' => $this->primaryKey(),
            'role_id' => $this->integer(4)->notNull(),
            'permission_id' => $this->integer(4)->notNull(),
            'is_active' => $this->boolean()->notNull(),
        ]);

        $this->createIndex('idx-role_permission_role_id_permission_id', 'role_permission', ['role_id', 'permission_id'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-role_permission_role_id_permission_id', 'role_permission');
        $this->dropTable('role_permission');
    }

}
