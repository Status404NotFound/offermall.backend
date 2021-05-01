<?php

use yii\db\Migration;

class m171019_092905_operator_pcs extends Migration
{
    public function safeUp()
    {
        $this->createTable('operator_pcs', [
            'operator_pcs_id' => $this->primaryKey(),
            'operator_id' => $this->integer(4)->notNull(),
            'order_id' => $this->integer(11)->notNull(),
            'pcs_old' => $this->integer(4)->notNull(),
            'pcs_new' => $this->integer(4)->notNull(),
            'up_sale' => $this->integer(4)->notNull(),
            'created_at' => $this->timestamp()->notNull(),
        ]);

        $this->addForeignKey('operator_pcs_FK_order_order_id', 'operator_pcs', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('operator_pcs_FK_user_id', 'operator_pcs', 'operator_id', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    public function safeDown()
    {
        $this->dropForeignKey('operator_pcs_FK_order_order_id', 'operator_pcs');
        $this->dropForeignKey('operator_pcs_FK_user_id', 'operator_pcs');
        $this->dropTable('operator_pcs');
    }

}
