<?php

use yii\db\Migration;

/**
 * Class m181018_103045_update_order_stickers_table
 */
class m181018_103045_update_order_stickers_table extends Migration
{
    /**
     * @var string Table name
     */
    protected $table = 'order_stickers';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $oldStickerId = $this->getStickerId('SHIPA DELIVERY');
        $newStickerId = $this->getStickerId('MARA KUWAIT');

        if ($oldStickerId && $newStickerId) {
            $this->update(
                $this->table,
                [
                    'sticker_id' => $newStickerId,
                ],
                [
                    'sticker_id' => $oldStickerId,
                ]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        /* impossible to revert action safeUp() */
    }

    /**
     * Get Sticker ID by Sticker Name
     *
     * @param $stickerName Sticker Name
     *
     * @return integer Sticker ID
     */
    public function  getStickerId($stickerName)
    {
        $table = 'delivery_stickers';
        $column = 'sticker_id';

        $sticker = (new \yii\db\Query())
            ->select($column)
            ->from($table)
            ->where(['sticker_name' => (string)$stickerName])
            ->limit(1)
            ->one();

        return (int)$sticker[$column];
    }
}
