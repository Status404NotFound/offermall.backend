<?php

namespace crm\models\delivery;

use common\models\delivery\UserDeliveryApi;
use Yii;
use common\models\BaseModel;

/**
 * This is the model class for table "delivery_stickers".
 *
 * @property int $sticker_id
 * @property string $sticker_name
 * @property string $sticker_color
 * @property int $owner_id
 * @property int $is_active
 * @property int $is_service
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property OrderStickers[] $orderStickers
 */
class DeliveryStickers extends BaseModel
{
    const IS_ACTIVE = 1;
    const NOT_ACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delivery_stickers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_id', 'is_active', 'is_service', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['sticker_name', 'sticker_color'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sticker_id' => Yii::t('app', 'Sticker ID'),
            'sticker_name' => Yii::t('app', 'Sticker Name'),
            'sticker_color' => Yii::t('app', 'Sticker Color'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'is_active' => Yii::t('app', 'Is Active'),
            'is_service' => Yii::t('app', 'Is Service'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active != 1;
    }

    public function inActive(): bool
    {
        return (bool)$this->updateAttributes([
            'is_active' => '0'
        ]);
    }

    public function isActive(): bool
    {
        return (bool)$this->updateAttributes([
            'is_active' => '1'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStickers()
    {
        return $this->hasMany(OrderStickers::className(), ['sticker_id' => 'sticker_id']);
    }
}
