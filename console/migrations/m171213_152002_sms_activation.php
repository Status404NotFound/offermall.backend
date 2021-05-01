<?php

use yii\db\Migration;

/**
 * Class m171213_152002_sms_activation
 */
class m171213_152002_sms_activation extends Migration
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
        $this->createTable('{{%sms_activation}}', [
            'sms_id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->notNull(),
            'hash' => $this->integer(11)->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);
        $this->addForeignKey('sms_activation_fk_user_id', 'sms_activation', 'user_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('sms_activation_fk_user_id', 'sms_activation');
        $this->dropTable('{{%sms_activation}}');
    }
}
