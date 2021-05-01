<?php

namespace common\models\geo;

use Yii;
use yii\db\ActiveRecord;
use common\models\geo\Countries;
use common\models\delivery\UserDeliveryApi;
use common\models\landing\LandingGeo;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\wm\WmOfferTarget;
use common\models\stock\Stock;
use common\models\delivery\DeliveryDate;

/**
 * This is the model class for table "geo".
 *
 * @property integer $id
 * @property integer $geo_id
 * @property string $geo_name
 * @property string $iso
 * @property integer $phone_code
 *
 * @property AdvertOfferTarget[] $advertOfferTargets
 * @property Countries $geo
 * @property GeoCity[] $geoCities
 * @property GeoRegion[] $geoRegions
 * @property LandingGeo[] $landingGeos
 * @property Stock[] $stocks
 * @property UserDeliveryApi[] $userDeliveryApis
 * @property WmOfferTarget[] $wmOfferTargets
 * @property DeliveryDate[] $blockDeliveryDates
 */
class Geo extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'geo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['geo_id', 'geo_name', 'iso', 'phone_code'], 'required'],
            [['geo_id', 'phone_code', 'phone_num_count'], 'integer'],
            [['geo_name'], 'string', 'max' => 255],
            [['iso'], 'string', 'max' => 3],
            [['geo_id'], 'unique'],
            [['geo_name'], 'unique'],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Countries::className(), 'targetAttribute' => ['geo_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'geo_name' => Yii::t('app', 'Geo Name'),
            'iso' => Yii::t('app', 'Iso'),
            'phone_code' => Yii::t('app', 'Phone Code'),
            'phone_num_count' => Yii::t('app', 'Phone Code'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertOfferTargets()
    {
        return $this->hasMany(AdvertOfferTarget::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Countries::className(), ['id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeoCities()
    {
        return $this->hasMany(GeoCity::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlockDeliveryDates()
    {
        return $this->hasMany(DeliveryDate::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeoRegions()
    {
        return $this->hasMany(GeoRegion::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLandingGeos()
    {
        return $this->hasMany(LandingGeo::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStocks()
    {
        return $this->hasMany(Stock::className(), ['location' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDeliveryApis()
    {
        return $this->hasMany(UserDeliveryApi::className(), ['country_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWmOfferTargets()
    {
        return $this->hasMany(WmOfferTarget::className(), ['geo_id' => 'geo_id']);
    }

    public static function getGeoByIso($iso){
        return self::find()
            ->where(['iso' => $iso])
            ->one();
    }

    public static function list()
    {
        return self::find()
            ->select('geo_id, geo_name, iso')
            ->asArray()
            ->all();
    }
}
