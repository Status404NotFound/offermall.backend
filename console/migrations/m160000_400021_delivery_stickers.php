<?php

use yii\db\Migration;

/**
 * Class m600000_400021_delivery_stickers
 */
class m160000_400021_delivery_stickers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('delivery_stickers', [
            'sticker_id' => $this->primaryKey(),
            'sticker_name' => $this->string('255'),
            'sticker_color' => $this->string('255'),
            'owner_id' => $this->integer('11'),
            'is_active' => $this->smallInteger('3')->defaultValue('1'),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11),
        ], $tableOptions);
        $this->createIndex('idx-delivery_stickers-owner_id', 'delivery_stickers', 'owner_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-delivery_stickers-owner_id', 'delivery_stickers');
        $this->dropTable('closed_financial_periods');
    }
}
