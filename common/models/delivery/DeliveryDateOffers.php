<?php

namespace common\models\delivery;

use Yii;
use common\models\geo\Geo;
use common\models\offer\Offer;

/**
 * This is the model class for table "{{%delivery_date_offers}}".
 *
 * @property int $delivery_date_offer_id
 * @property int $delivery_date_id
 * @property int $offer_id
 * @property int $geo_id
 *
 * @property DeliveryDate[] $deliveryDate
 * @property Geo $geo
 * @property Offer $offer
 */
class DeliveryDateOffers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_date_offers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delivery_date_id', 'offer_id', 'geo_id'], 'required'],
            [['delivery_date_id', 'offer_id', 'geo_id'], 'integer'],
            [['delivery_date_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeliveryDate::className(), 'targetAttribute' => ['delivery_date_id' => 'delivery_date_id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'delivery_date_offer_id' => Yii::t('app', 'Delivery Date Offer ID'),
            'delivery_date_id' => Yii::t('app', 'Delivery Date ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryDate()
    {
        return $this->hasMany(DeliveryDate::className(), ['delivery_date_id' => 'delivery_date_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }
}
