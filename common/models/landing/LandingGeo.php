<?php

namespace common\models\landing;

use common\models\finance\Currency;
use common\models\geo\Geo;
use Yii;

/**
 * This is the model class for table "landing_geo".
 *
 * @property integer $landing_geo_id
 * @property integer $landing_id
 * @property integer $geo_id
 * @property integer $old_price
 * @property integer $new_price
 * @property integer $discount
 * @property integer $currency_id
 *
 * @property Currency $currency
 * @property Geo $geo
 * @property Landing $landing
 */
class LandingGeo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'landing_geo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['landing_id', 'geo_id', 'old_price', 'new_price', 'discount', 'currency_id'], 'required'],
            [['landing_id', 'geo_id', 'currency_id'], 'integer'],
            [['old_price', 'new_price', 'discount'], 'string'],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::className(), 'targetAttribute' => ['landing_id' => 'landing_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'landing_geo_id' => 'Landing Geo ID',
            'landing_id' => 'Landing ID',
            'geo_id' => 'Geo ID',
            'old_price' => 'Old Price',
            'new_price' => 'New Price',
            'discount' => 'Discount',
            'currency_id' => 'Currency ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['currency_id' => 'currency_id']);
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
    public function getLanding()
    {
        return $this->hasOne(Landing::className(), ['landing_id' => 'landing_id']);
    }
}
