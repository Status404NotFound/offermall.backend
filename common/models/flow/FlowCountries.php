<?php

namespace common\models\flow;

use Yii;
use common\models\BaseModel;

/**
 * This is the model class for table "flow_countries".
 *
 * @property integer $id
 * @property integer $flow_id
 * @property integer $country_id
 *
 * @property Flow $flow
 */
class FlowCountries extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'flow_countries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flow_id', 'country_id'], 'required'],
            [['flow_id', 'country_id'], 'integer'],
            [['flow_id', 'country_id'], 'unique', 'targetAttribute' => ['flow_id', 'country_id'], 'message' => 'The combination of Flow ID and Country ID has already been taken.'],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
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
            'country_id' => Yii::t('app', 'Country ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['flow_id' => 'flow_id']);
    }
}
