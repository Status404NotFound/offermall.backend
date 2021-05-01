<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_advert`.
 */
class m120000_100002_target_advert extends Migration
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

        $this->createTable('{{%target_advert}}', [
            'target_advert_id' => $this->primaryKey(11),

            'target_advert_group_id' => $this->integer(11)->notNull(),
            'advert_id' => $this->integer(11)->notNull(),
            'stock_id' => $this->integer(11)->null(),
            'active' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'pay_online' => $this->smallInteger(1)->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('target_advert_uq1', 'target_advert', ['target_advert_group_id', 'advert_id'], true);

        $this->addForeignKey('target_advert_fk_target_advert_group_id', 'target_advert', 'target_advert_group_id', 'target_advert_group', 'target_advert_group_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_fk_advert_id', 'target_advert', 'advert_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('target_advert_fk_stock_id', 'target_advert', 'stock_id', 'stock', 'stock_id', 'NO ACTION', 'NO ACTION');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_advert_fk_target_advert_group_id', 'target_advert');
        $this->dropForeignKey('target_advert_fk_advert_id', 'target_advert');
        $this->dropForeignKey('target_advert_fk_stock_id', 'target_advert');
        $this->dropIndex('target_advert_uq1', 'target_advert');

        $this->dropTable('{{%target_advert}}');
    }
}
