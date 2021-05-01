<?php

namespace common\models\geo;

use Yii;
use common\models\customer\Customer;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "geo_city".
 *
 * @property integer $city_id
 * @property string $city_name
 * @property integer $region_id
 * @property integer $geo_id
 *
 * @property Customer[] $customers
 * @property Geo $geo
 * @property GeoRegion $region
 */
class GeoCity extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'geo_city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['city_name', 'geo_id'], 'required'],
            [['region_id', 'geo_id'], 'integer'],
            [['city_name'], 'string', 'max' => 255],
            [['city_name'], 'unique'],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeoRegion::className(), 'targetAttribute' => ['region_id' => 'region_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'city_id' => Yii::t('app', 'City ID'),
            'city_name' => Yii::t('app', 'City Name'),
            'region_id' => Yii::t('app', 'Region ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['city_id' => 'city_id']);
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
    public function getRegion()
    {
        return $this->hasOne(GeoRegion::className(), ['region_id' => 'region_id']);
    }

    /**
     * @param $city_id
     * @return mixed
     */
    public static function getGeoCityName($city_id)
    {
        $result = self::find()
            ->select(['country_name'])
            ->where(['city_id' => $city_id])
            ->asArray()
            ->one();

        return ArrayHelper::getValue($result, 'country_name');
    }
}