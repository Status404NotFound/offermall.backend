<?php

namespace common\models\callcenter;

use Yii;
use common\models\Language;
use common\models\order\Order;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "call_list".
 *
 * @property integer $order_id
 * @property string $time_to_call
 * @property string $updated_at
 * @property integer $attempts
 * @property integer $lead_status
 * @property integer $lead_state
 * @property integer $language_id
 * @property integer $operator_id
 * @property integer $queue_id
 *
 * @property Language $language
 * @property User $operator
 * @property Order $order
 */
class CallList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_to_call', 'updated_at'], 'safe'],
            [['attempts', 'lead_status', 'lead_state', 'language_id', 'operator_id', 'queue_id'], 'integer'],
            [['lead_status', 'lead_state', 'language_id'], 'required'],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_id' => 'language_id']],
            [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['operator_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'time_to_call' => Yii::t('app', 'Time To Call'),
            'attempts' => Yii::t('app', 'Attempts'),
            'lead_status' => Yii::t('app', 'Lead Status'),
            'lead_state' => Yii::t('app', 'Lead State'),
            'language_id' => Yii::t('app', 'Language ID'),
            'operator_id' => Yii::t('app', 'Operator ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['language_id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(User::className(), ['id' => 'operator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }
}
