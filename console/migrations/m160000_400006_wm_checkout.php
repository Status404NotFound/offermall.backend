<?php

use yii\db\Migration;

class m160000_400006_wm_checkout extends Migration
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

        $this->createTable('{{%wm_checkout}}', [
            'wm_checkout_id' => $this->primaryKey(11),
            'wm_id' => $this->integer(11)->notNull(),
            'amount' => $this->float()->notNull(),
            'comment' => $this->string(255)->null(),
            'status' => $this->smallInteger(2)->defaultValue(0),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->addForeignKey('wm_checkout_fk_wm_id', 'wm_checkout', 'wm_id', 'user', 'id', 'CASCADE', 'CASCADE');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('wm_checkout_fk_wm_id', 'wm_checkout');

        $this->dropTable('{{%wm_checkout}}');
    }
}
