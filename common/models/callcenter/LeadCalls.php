<?php

namespace common\models\callcenter;

use Yii;
use common\models\order\OrderSku;

/**
 * This is the model class for table "lead_calls".
 *
 * @property integer $id
 * @property integer $operator_id
 * @property integer $order_id
 * @property integer $duration
 * @property boolean $call_checked
 * @property integer $call_id
 * @property string $datetime
 *
 * @property OrderSku[] $orderSku
 */
class LeadCalls extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lead_calls';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_id', 'order_id', 'duration', 'call_id'], 'integer'],
            [['call_checked'], 'boolean'],
            [['datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operator_id' => 'Operator ID',
            'order_id' => 'Order ID',
            'duration' => 'Duration',
            'call_id' => 'Call ID',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderSku()
    {
        return $this->hasMany(OrderSku::className(), ['order_id' => 'order_id']);
    }
}
