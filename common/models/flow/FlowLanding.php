<?php

namespace common\models\flow;

use Yii;
use common\models\BaseModel;
use common\models\landing\Landing;

/**
 * This is the model class for table "{{%flow_landing}}".
 *
 * @property integer $id
 * @property integer $flow_id
 * @property integer $landing_id
 *
 * @property Flow $flow
 * @property Landing $landing
 */
class FlowLanding extends BaseModel
{
    public $created_at = false;
    public $updated_at = false;
    public $created_by = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%flow_landing}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flow_id', 'landing_id'], 'required'],
            [['flow_id', 'landing_id'], 'integer'],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
            [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::className(), 'targetAttribute' => ['landing_id' => 'landing_id']],
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
            'landing_id' => Yii::t('app', 'Landing ID'),
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
    public function getLanding()
    {
        return $this->hasOne(Landing::className(), ['landing_id' => 'landing_id']);
    }

    /**
     * @param $flow_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getFlowLandings($flow_id)
    {
        return self::find()
            ->select([
                'flow_landing.flow_id',
                'flow_landing.landing_id',
                'landing.url',
            ])
            ->join('LEFT JOIN', 'landing', 'flow_landing.landing_id = landing.landing_id')
            ->where(['flow_landing.flow_id' => $flow_id])
            ->asArray()
            ->all();
    }
}
