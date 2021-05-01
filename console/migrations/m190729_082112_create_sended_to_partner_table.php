<?php

use yii\db\Migration;

/**
 * Handles the creation of table `sended_to_partner`.
 */
class m190729_082112_create_sended_to_partner_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('sended_to_partner', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(11)->notNull(),
            'partner_id' => $this->integer(11)->notNull(),
            'remote_order_id' => $this->integer(11)->notNull(),

            'created_by' => $this->integer()->null(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);

        $this->createIndex('sended_to_partner_Idx_order_id_partner_id', 'sended_to_partner', ['order_id', 'partner_id'], true);

        $this->addForeignKey('sended_to_partner_fk_order_id', 'sended_to_partner', 'order_id', 'order', 'order_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('sended_to_partner_fk_partner_id', 'sended_to_partner', 'partner_id', 'partner_crm', 'id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('sended_to_partner_fk_created_by', 'sended_to_partner', 'created_by', 'user', 'id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('sended_to_partner_fk_order_id', 'sended_to_partner');
        $this->dropForeignKey('sended_to_partner_fk_partner_id', 'sended_to_partner');
        $this->dropForeignKey('sended_to_partner_fk_created_by', 'sended_to_partner');

        $this->dropTable('sended_to_partner');
    }
}
