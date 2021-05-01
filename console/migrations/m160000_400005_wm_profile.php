<?php

use yii\db\Migration;

class m160000_400005_wm_profile extends Migration
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

        $this->createTable('{{%wm_profile}}', [
            'wm_profile_id' => $this->primaryKey(11),
            'wm_id' => $this->integer(11)->notNull(),
            'skype' => $this->string(255)->null(),
            'telegram' => $this->string(255)->null(),
            'facebook' => $this->string(255)->null(),
            'card' => $this->string(255)->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',

        ], $tableOptions);

        $this->addForeignKey('wm_profile_fk_wm_id', 'wm_profile', 'wm_id', 'user', 'id', 'CASCADE', 'CASCADE');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('wm_profile_fk_wm_id', 'wm_profile');

        $this->dropTable('{{%wm_profile}}');
    }
}
