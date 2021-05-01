<?php

namespace crm\models\delivery;

use Yii;
use common\models\order\Order;

/**
 * This is the model class for table "order_stickers".
 *
 * @property int $order_id
 * @property int $sticker_id
 *
 * @property Order $order
 * @property DeliveryStickers $sticker
 */
class OrderStickers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_stickers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sticker_id'], 'integer'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['sticker_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeliveryStickers::className(), 'targetAttribute' => ['sticker_id' => 'sticker_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'sticker_id' => Yii::t('app', 'Sticker ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSticker()
    {
        return $this->hasMany(DeliveryStickers::className(), ['sticker_id' => 'sticker_id']);
    }

    /**
     * @param $order_id
     * @return array|\common\models\finance\Currency[]|\common\models\geo\Countries[]|\common\models\geo\Geo[]|\common\models\geo\GeoRegion[]|\common\models\offer\targets\advert\TargetAdvert[]|\common\models\offer\targets\advert\TargetAdvertGroupRules[]|\common\models\offer\targets\wm\TargetWmGroupRules[]|\common\models\order\OrderView[]|\common\modules\user\models\tables\User[]|DeliveryStickers[]|OrderStickers[]|\yii\db\ActiveRecord[]
     */
    public static function findOrderStickersByOrderId($order_id)
    {
        $result = self::find()
            ->select([
                'delivery_stickers.sticker_id',
                'order_stickers.order_id',
                'delivery_stickers.sticker_name',
                'delivery_stickers.sticker_color',
            ])
            ->leftJoin('delivery_stickers', 'delivery_stickers.sticker_id = order_stickers.sticker_id')
            ->where(['delivery_stickers.is_active' => 1])
            ->andWhere(['order_stickers.order_id' => $order_id])
            ->andWhere(['delivery_stickers.is_service' => 1])
            ->andWhere(['delivery_stickers.owner_id' => Yii::$app->user->identity->getOwnerId()])
            ->asArray()
            ->all();

        return $result;
    }
}
