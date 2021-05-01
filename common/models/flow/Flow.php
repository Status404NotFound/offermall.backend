<?php

namespace common\models\flow;

use Yii;
use common\models\BaseModel;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\Offer;
use common\models\landing\Landing;
use common\models\webmaster\parking\ParkingDomain;

/**
 * This is the model class for table "flow".
 *
 * @property integer $flow_id
 * @property integer $offer_id
 * @property integer $wm_id
 * @property integer $advert_offer_target_status
 * @property string $flow_key
 * @property string $flow_name
 * @property string $traffic_back
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $use_tds
 * @property integer $active
 * @property integer $is_deleted
 *
 * @property TargetWm $targetWm
 * @property FlowCountries[] $flowCountries
 * @property FlowLanding[] $flowLands
 * @property FlowLanding[] $lands
 * @property FlowTransit[] $flowTransits
 * @property FlowTransit[] $transits
 * @property ParkingDomain[] $parking
 */
class Flow extends BaseModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_NON_ACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'flow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'wm_id', 'advert_offer_target_status', 'flow_name', 'flow_key'], 'required'],
            [['offer_id', 'wm_id', 'advert_offer_target_status', 'use_tds', 'active', 'is_deleted', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['flow_name', 'flow_key', 'traffic_back'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'flow_id' => Yii::t('app', 'Flow ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'advert_offer_target_status' => Yii::t('app', 'Advert Offer Target Status'),
            'flow_name' => Yii::t('app', 'Flow Name'),
            'flow_key' => Yii::t('app', 'Flow Key'),
            'traffic_back' => Yii::t('app', 'Traffic Back'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'use_tds' => Yii::t('app', 'Use Tds'),
            'active' => Yii::t('app', 'Active'),
            'is_deleted' => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetWm()
    {
        return $this->hasOne(TargetWm::className(), ['advert_offer_target_status' => 'advert_offer_target_status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlowCountries()
    {
        return $this->hasMany(FlowCountries::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow_landings()
    {
        return $this->hasMany(Landing::className(), ['landing_id' => 'landing_id'])->viaTable('flow_land', ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer_landings()
    {
        return $this->hasMany(Landing::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlowTransits()
    {
        return $this->hasMany(FlowTransit::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransits()
    {
        return $this->hasMany(FlowTransit::className(), ['transit_id' => 'transit_id'])->viaTable('flow_transit', ['flow_id' => 'flow_id']);
    }

    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParking()
    {
        return $this->hasOne(ParkingDomain::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @inheritdoc
     * @return FlowQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FlowQuery(get_called_class());
    }

    /**
     * @return array|Flow[]
     */
    public static function getFlowsList()
    {
        $wm_id = Yii::$app->user->identity->getId();

        return Flow::find()
            ->select(['flow_id', 'flow_name'])
            ->active()
            ->where(['wm_id' => $wm_id])
            ->asArray()
            ->all();
    }

    public static function getFlowsByWmId($wm_id = null)
    {
        if($wm_id == null){
            $wm_id = Yii::$app->user->identity->getId();
        }

        return Flow::find()
            ->active()
            ->where(['wm_id' => $wm_id])
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getWmFlowId()
    {
        $wm_flows_id_array = [];
        $query = Flow::find()
            ->select('flow_id');

        if (!is_null(Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getId()]);

        $wm_flows = $query->asArray()->all();

        foreach ($wm_flows as $wm_flow) {
            $wm_flows_id_array[] = $wm_flow['flow_id'];
        }
        return $wm_flows_id_array;
    }
}