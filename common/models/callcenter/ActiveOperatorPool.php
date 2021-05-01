<?php

namespace frontend\models;

use common\models\callcenter\OperatorConf;
use common\models\order\Order;
use Yii;

/**
 * This is the model class for table "active_operator_pool".
 *
 * @property integer $operator_id
 * @property integer $order_id
 *
 * @property OperatorConf $operator
 * @property Order $order
 */
class ActiveOperatorPool extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'active_operator_pool';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_id', 'order_id'], 'required'],
            [['operator_id', 'order_id'], 'integer'],
            [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => OperatorConf::className(), 'targetAttribute' => ['operator_id' => 'operator_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'operator_id' => Yii::t('app', 'Operator ID'),
            'order_id' => Yii::t('app', 'Order ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(OperatorConf::className(), ['operator_id' => 'operator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }
}
