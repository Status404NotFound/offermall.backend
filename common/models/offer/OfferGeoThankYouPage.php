<?php

namespace common\models\offer;

use common\models\geo\Geo;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "offer_geo_thank_you_page".
 *
 * @property integer $id
 * @property integer $offer_id
 * @property integer $geo_id
 * @property integer $url
 *
 * @property Offer $offer
 * @property Geo $geo
 */
class OfferGeoThankYouPage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer_geo_thank_you_page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'geo_id'], 'required'],
            [['offer_id', 'geo_id'], 'integer'],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'url' => Yii::t('app', 'Url'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
    }
}
