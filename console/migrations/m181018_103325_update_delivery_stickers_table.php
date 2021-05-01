<?php

use yii\db\Migration;

/**
 * Class m181018_103325_update_delivery_stickers_table
 */
class m181018_103325_update_delivery_stickers_table extends Migration
{
    /**
     * @var string Table name
     */
    protected $table = 'delivery_stickers';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update(
            $this->table,
            [
                'sticker_name' => 'SHIPA KUWAIT',
            ],
            [
                'sticker_name' => 'MARA KUWAIT',
            ]
        );

        $this->delete(
            $this->table,
            [
                'sticker_name' => 'SHIPA DELIVERY',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->insert(
            $this->table,
            [
                'sticker_name' => 'SHIPA DELIVERY',
                'sticker_color' => '#cbc0ff',
                'owner_id' => 6,
                'is_active' => 1,
                'is_service' => 1,
                'created_by' => 1,
            ]
        );

        $this->update(
            $this->table,
            [
                'sticker_name' => 'MARA KUWAIT',
            ],
            [
                'sticker_name' => 'SHIPA KUWAIT',
            ]
        );
    }
}
