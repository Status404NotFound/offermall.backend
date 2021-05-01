<?php

use yii\db\Migration;

/**
 * Class m600000_400022_order_stickers
 */
class m160000_400022_order_stickers extends Migration
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
        $this->createTable('order_stickers', [
            'order_id' => $this->primaryKey(),
            'sticker_id' => $this->integer('11'),
        ], $tableOptions);

        $this->createIndex('idx-order_stickers-order_id', 'order_stickers', 'order_id');
        $this->createIndex('idx-order_stickers-sticker_id', 'order_stickers', 'sticker_id');

        $this->addForeignKey('fk-order_stickers-order_id', 'order_stickers', 'order_id', 'order', 'order_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-order_stickers-order_stickers', 'order_stickers', 'sticker_id', 'delivery_stickers', 'sticker_id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-order_stickers-order_id', 'order_stickers');
        $this->dropIndex('idx-order_stickers-sticker_id', 'order_stickers');

        $this->dropForeignKey('fk-order_stickers-order_id', 'order_stickers');
        $this->dropForeignKey('fk-order_stickers-order_stickers', 'order_stickers');

        $this->dropTable('order_stickers');
    }
}
