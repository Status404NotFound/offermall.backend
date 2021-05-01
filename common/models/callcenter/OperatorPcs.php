<?php

namespace common\models\callcenter;

use common\models\order\Order;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "operator_pcs".
 *
 * @property integer $operator_pcs_id
 * @property integer $operator_id
 * @property integer $order_id
 * @property integer $pcs_old
 * @property integer $pcs_new
 * @property integer $up_sale
 * @property string $created_at
 *
 * @property Order $order
 * @property User $operator
 */
class OperatorPcs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_pcs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_id', 'order_id', 'pcs_old', 'pcs_new', 'up_sale'], 'required'],
            [['operator_id', 'order_id', 'pcs_old', 'pcs_new', 'up_sale'], 'integer'],
            [['created_at'], 'safe'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['operator_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'operator_pcs_id' => 'Operator Pcs ID',
            'operator_id' => 'Operator ID',
            'order_id' => 'Order ID',
            'pcs_old' => 'Pcs Old',
            'pcs_new' => 'Pcs New',
            'up_sale' => 'Up Sale',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(User::className(), ['id' => 'operator_id']);
    }
}
