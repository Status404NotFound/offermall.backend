<?php

namespace crm\services\delivery;

use Yii;
use common\services\delivery\DeliveryException;
use crm\models\delivery\DeliveryStickers;
use crm\models\delivery\OrderStickers;

/**
 * Class DeliveryStickersService
 * @package crm\services\delivery
 */
class DeliveryStickersService
{
    /**
     * @return array|DeliveryStickers[]|OrderStickers[]|\yii\db\ActiveRecord[]
     */
    public function stickerList()
    {
        $stickers_list = DeliveryStickers::find()
            ->select([
                'sticker_id',
                'sticker_name',
                'sticker_color',
                'is_active',
                'is_service'
            ])
            ->where(['owner_id' => Yii::$app->user->identity->getOwnerId()])
            ->orWhere(['owner_id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->all();

        return $stickers_list;
    }

    /**
     * @param array $request
     * @return array|bool
     */
    public function createSticker(array $request)
    {
        $sticker = new DeliveryStickers();
        $sticker->setAttributes([
            'sticker_name' => $request['sticker_name'],
            'sticker_color' => $request['sticker_color'],
            'owner_id' => Yii::$app->user->identity->getId(),
            'is_service' => (bool)$request['is_service'],
        ]);

        return $sticker->validate() ? $sticker->save() : $sticker->errors;
    }

    /**
     * @param string $sticker
     * @return bool
     * @throws DeliveryException
     */
    public function changeStatus(string $sticker): bool
    {
        $sticker = DeliveryStickers::findOne(['sticker_id' => $sticker, 'is_service' => 0]);

        if (!$sticker)
            throw new DeliveryException('Error!');

        if ($sticker->getIsActive()) {
            return $sticker->isActive();
        } else {
            return $sticker->inActive();
        }
    }

    /**
     * @param array $request
     * @return array|bool
     */
    public function updateSticker(array $request)
    {
        $sticker = DeliveryStickers::findOne(['sticker_id' => $request['sticker_id']]);

        $sticker->setAttributes([
           'sticker_name' => $request['sticker_name'],
           'sticker_color' => $request['sticker_color'],
           'owner_id' => Yii::$app->user->identity->getId(),
           'is_service' => $request['is_service'],
        ]);

        return $sticker->validate() ? $sticker->save() : $sticker->errors;
    }

    /**
     * @param string $sticker_id
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteSticker(string $sticker_id)
    {
        $sticker = DeliveryStickers::findOne(['sticker_id' => $sticker_id, 'is_service' => 0]);

        if (!$sticker)
            throw new DeliveryException('Error!');

        return $sticker->delete();
    }

    /**
     * @param $order_id
     * @param array $stickers
     * @throws DeliveryException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveOrderStickers($order_id, array $stickers)
    {
        if ($this->checkIfOrderStickerExist($order_id, $stickers)) {
            foreach ($stickers as $sticker) {
                $order_sticker = new OrderStickers();
                $order_sticker->setAttributes([
                    'order_id' => $order_id,
                    'sticker_id' => $sticker
                ]);

                if (!$order_sticker->save()){
                    throw new DeliveryException('Error! Stickers are not attached.');
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getBaseDeliverySticker()
    {
        $colors_array = ['#e59290', '#e5ac90', '#e5ce90', '#cae590', '#97e590', '#90e5b4',
            '#90e5e0', '#90bee5', '#9890e5', '#ca90e5', '#e590bd', '#e59099',
        ];
        $color = array_rand($colors_array);

        return $colors_array[$color];
    }

    /**
     * @param array $orders
     * @param array $stickers
     * @return bool
     */
    public function deleteOrderStickers(array $orders, array $stickers)
    {
        $stickers = OrderStickers::findAll(['order_id' => $orders, 'sticker_id' => $stickers]);
        foreach ($stickers as $sticker){
            $sticker->delete();
        }

        return true;
    }

    /**
     * @param array $stickers
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function checkIfOrderStickerExist($order_id, $stickers)
    {
        $order_sticker = OrderStickers::find()
            ->joinWith('sticker')
            ->where(['order_stickers.order_id' => $order_id])
            ->andWhere(['order_stickers.sticker_id' => $stickers])
            ->all();

        foreach ($order_sticker as $sticker){
            $sticker->delete();
        }

        return true;
    }
}