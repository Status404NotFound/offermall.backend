<?php

use yii\db\Migration;

/**
 * Class m600000_400012_parking_domain
 */
class m160000_400012_parking_domain extends Migration
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

        $this->createTable('{{%parking_domain}}', [
            'domain_id' => $this->primaryKey(11),
            'domain_name' => $this->string(255)->notNull(),
            'flow_id' => $this->integer(11)->notNull(),
            'wm_id' => $this->integer(11)->notNull(),
            'geo_id' => $this->integer(11),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11),
            'updated_by' => $this->integer(11),

            'active' => $this->smallInteger(2)->defaultValue(1),
            'is_deleted' => $this->smallInteger(2)->defaultValue(0),
        ], $tableOptions);

        $this->addForeignKey('parking_fk_flow_id', 'parking_domain', 'flow_id', 'flow', 'flow_id');
        $this->addForeignKey('parking_fk_wm_id', 'parking_domain', 'wm_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('parking_fk_flow_id', 'parking_domain');
        $this->dropForeignKey('parking_fk_wm_id', 'parking_domain');

        $this->dropTable('{{%parking_domain}}');
    }
}
