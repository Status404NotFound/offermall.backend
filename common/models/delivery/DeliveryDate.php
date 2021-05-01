<?php

namespace common\models\delivery;

use Yii;
use common\models\BaseModel;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "{{%delivery_date}}".
 *
 * @property int $delivery_date_id
 * @property int $advert_id
 * @property string $delivery_dates
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property User $advert
 * @property DeliveryDateOffers[] $deliveryDateOffers
 */
class DeliveryDate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_date}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advert_id'], 'required'],
            [['advert_id', 'created_by', 'updated_by'], 'integer'],
            [['delivery_dates'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['advert_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'delivery_date_id' => Yii::t('app', 'Delivery Date ID'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'delivery_dates' => Yii::t('app', 'Delivery Dates'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvert()
    {
        return $this->hasOne(User::className(), ['id' => 'advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryDateOffers()
    {
        return $this->hasMany(DeliveryDateOffers::className(), ['delivery_date_id' => 'delivery_date_id']);
    }
}
