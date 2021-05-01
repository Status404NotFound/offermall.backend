<?php

use yii\db\Migration;

/**
 * Class m600000_400019_user_requisites
 */
class m160000_400019_user_requisites extends Migration
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
        $this->createTable('user_requisites', [
            'requisite_id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'geo_id' => $this->integer(11)->notNull(),
            'description' => $this->text()->null(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11),
        ], $tableOptions);

        $this->addForeignKey('fk-user_requisites-user_id', 'user_requisites', 'user_id', 'user', 'id');
        $this->addForeignKey('fk-user_requisites-geo', 'user_requisites', 'geo_id', 'geo', 'geo_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user_requisites-user_id', 'user_requisites');
        $this->dropForeignKey('fk-user_requisites-geo', 'user_requisites');

        $this->dropTable('user_requisites');
    }
}
