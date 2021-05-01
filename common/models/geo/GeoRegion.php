<?php

namespace common\models\geo;

use Yii;
use common\models\customer\Customer;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "geo_region".
 *
 * @property integer $region_id
 * @property string $region_name
 * @property integer $geo_id
 *
 * @property Customer[] $customers
 * @property GeoCity[] $geoCities
 * @property Geo $geo
 */
class GeoRegion extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'geo_region';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['region_name', 'geo_id'], 'required'],
            [['geo_id'], 'integer'],
            [['region_name'], 'string', 'max' => 255],
            [['region_name'], 'unique'],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'region_id' => Yii::t('app', 'Region ID'),
            'region_name' => Yii::t('app', 'Region Name'),
            'geo_id' => Yii::t('app', 'Geo ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['region_id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeoCities()
    {
        return $this->hasMany(GeoCity::className(), ['region_id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @param $region_id
     * @return mixed
     */
    public static function getRegionName($region_id)
    {
        $result = self::find()
            ->select(['region_name'])
            ->where(['region_id' => $region_id])
            ->asArray()
            ->one();

        return ArrayHelper::getValue($result, 'region_name');
    }
}