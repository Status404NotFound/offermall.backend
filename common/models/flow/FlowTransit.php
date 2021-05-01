<?php

namespace common\models\flow;

use Yii;
use yii\db\ActiveRecord;
use common\models\offer\OfferTransit;

/**
 * This is the model class for table "flow_transit".
 *
 * @property integer $id
 * @property integer $flow_id
 * @property integer $transit_id
 *
 * @property Flow $flow
 * @property OfferTransit $transit
 */
class FlowTransit extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'flow_transit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flow_id', 'transit_id'], 'required'],
            [['flow_id', 'transit_id'], 'integer'],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
            [['transit_id'], 'exist', 'skipOnError' => true, 'targetClass' => OfferTransit::className(), 'targetAttribute' => ['transit_id' => 'transit_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'transit_id' => Yii::t('app', 'Transit ID'),
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
    public function getTransit()
    {
        return $this->hasOne(OfferTransit::className(), ['transit_id' => 'transit_id']);
    }

    /**
     * @param $flow_id
     * @return array|FlowLanding[]|FlowTransit[]|\common\models\landing\Landing[]|\common\models\LandingViews[]|\common\models\webmaster\WmCheckout[]|\common\models\webmaster\WmProfile[]|ActiveRecord[]
     */
    public static function getFlowTransits($flow_id)
    {
        return self::find()
            ->select([
                'flow_transit.flow_id',
                'flow_transit.transit_id',
                'offer_transit.url',
            ])
            ->join('LEFT JOIN', 'offer_transit', 'flow_transit.transit_id = offer_transit.transit_id')
            ->where(['flow_transit.flow_id' => $flow_id])
            ->asArray()
            ->all();
    }
}
