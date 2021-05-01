<?php

use yii\db\Migration;

/**
 * Class m600000_400015_delivery_date
 */
class m160000_400015_delivery_date extends Migration
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
        $this->createTable('delivery_date', [
            'delivery_date_id' => $this->primaryKey(),
            'advert_id' => $this->integer(11)->notNull(),
            'delivery_dates' => $this->text()->null(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11),
        ], $tableOptions);

        $this->addForeignKey('delivery_date_fk_owner_id', 'delivery_date', 'advert_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('delivery_date_fk_owner_id', 'delivery_date');

        $this->dropTable('delivery_date');
    }
}
