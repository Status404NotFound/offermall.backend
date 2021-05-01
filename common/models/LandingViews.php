<?php

namespace common\models;

use common\models\flow\Flow;
use common\models\offer\Offer;
use Yii;

/**
 * This is the model class for table "landing_views".
 *
 * @property integer $landing_views_id
 * @property string $date
 * @property integer $landing_id
 * @property integer $flow_id
 * @property integer $geo_id
 * @property integer $offer_id
 * @property integer $views
 * @property integer $uniques
 * @property string $sub_id_1
 * @property string $sub_id_2
 * @property string $sub_id_3
 * @property string $sub_id_4
 * @property string $sub_id_5
 * @property string $updated_at
 *
 * @property Flow $flow
 * @property Offer $offer
 */
class LandingViews extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'landing_views';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date', 'updated_at'], 'safe'],
            [['landing_id', 'flow_id', 'offer_id', 'views', 'uniques', 'geo_id'], 'integer'],
            [['sub_id_1', 'sub_id_2', 'sub_id_3', 'sub_id_4', 'sub_id_5'], 'string', 'max' => 255],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'landing_views_id' => Yii::t('app', 'Landing Views ID'),
            'date' => Yii::t('app', 'Date'),
            'landing_id' => Yii::t('app', 'Landing ID'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'views' => Yii::t('app', 'Views'),
            'uniques' => Yii::t('app', 'Uniques'),
            'sub_id_1' => Yii::t('app', 'Sub Id 1'),
            'sub_id_2' => Yii::t('app', 'Sub Id 2'),
            'sub_id_3' => Yii::t('app', 'Sub Id 3'),
            'sub_id_4' => Yii::t('app', 'Sub Id 4'),
            'sub_id_5' => Yii::t('app', 'Sub Id 5'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }
}
