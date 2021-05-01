<?php

use yii\db\Migration;

class m160000_400009_postback_global extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%postback_global}}', [
            'postback_id' => $this->primaryKey(11),
            'wm_id' => $this->integer(11)->notNull(),
            'url' => $this->text()->null(),
            'url_approved' => $this->text()->null(),
            'url_cancelled' => $this->text()->null(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->addForeignKey('postback_global_fk_wm_id', 'postback_global', 'wm_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('postback_global_fk_wm_id', 'postback_global');

        $this->dropTable('postback_global');
    }
}
