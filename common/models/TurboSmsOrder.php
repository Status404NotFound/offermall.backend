<?php

namespace common\models;

use Yii;
use common\models\order\Order;
use avator\turbosms\models\TurboSmsSent;

/**
 * This is the model class for table "{{%turbo_sms_order}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $turbo_sms_id
 * @property integer $sms_status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Order $order
 * @property TurboSmsSent $turboSms
 */
class TurboSmsOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%turbo_sms_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'turbo_sms_id'], 'required'],
            [['order_id', 'turbo_sms_id', 'sms_status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['turbo_sms_id'], 'exist', 'skipOnError' => true, 'targetClass' => TurboSmsSent::className(), 'targetAttribute' => ['turbo_sms_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'turbo_sms_id' => Yii::t('app', 'Turbo Sms ID'),
            'sms_status' => Yii::t('app', 'Sms Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
    public function getTurboSms()
    {
        return $this->hasOne(TurboSmsSent::className(), ['id' => 'turbo_sms_id']);
    }
}
