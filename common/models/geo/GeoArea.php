<?php

namespace common\models\geo;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "geo_area".
 *
 * @property int $area_id
 * @property string $main_area
 * @property string $sub_area
 * @property string $area_known_locally
 * @property int $region_id
 *
 * @property GeoRegion $region
 */
class GeoArea extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'geo_area';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_id'], 'required'],
            [['region_id'], 'integer'],
            [['main_area', 'sub_area', 'area_known_locally'], 'string', 'max' => 255],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeoRegion::className(), 'targetAttribute' => ['region_id' => 'region_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'area_id' => 'Area ID',
            'main_area' => 'Main Area',
            'sub_area' => 'Sub Area',
            'area_known_locally' => 'Area Known Locally',
            'region_id' => 'Region ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(GeoRegion::className(), ['region_id' => 'region_id']);
    }

    /**
     * @param $area_id
     * @return mixed
     */
    public static function getAreaName($area_id)
    {
        $result = self::find()
            ->select([
                'CONCAT(sub_area, " / ", area_known_locally) as area_name'
            ])
            ->where(['area_id' => $area_id])
            ->asArray()
            ->one();

        return ArrayHelper::getValue($result, 'area_name');
    }
}
