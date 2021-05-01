<?php

use yii\db\Migration;

/**
 * Class m180531_132753_active_operator_pool
 */
class m180531_132753_active_operator_pool extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('active_operator_pool', [
            'operator_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('active_operator_pool_FK_operator_id', 'active_operator_pool' ,'operator_id', 'operator_conf', 'operator_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('active_operator_pool_Fk_order_id', 'active_operator_pool', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('active_operator_pool_FK_operator_id', 'active_operator_pool');
        $this->dropForeignKey('active_operator_pool_Fk_order_id', 'active_operator_pool');
        $this->dropTable('active_operator_pool');
    }

}
