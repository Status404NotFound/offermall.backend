<?php

namespace common\models\log\orderInfo;

use common\models\BaseModel;
use common\models\customer\Customer;
use common\models\order\Order;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "order_info_log".
 *
 * @property integer $order_info_log_id
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $customer_id
 * @property integer $order_data_id
 * @property string $model
 * @property string $field_name
 * @property string $last_value
 * @property string $new_value
 * @property integer $instrument
 * @property string $ip
 * @property string $city
 * @property string $datetime
 * @property string $comment
 *
 * @property Customer $customer
 * @property Order $order
 * @property User $user
 */
class OrderInfoLog extends BaseModel
{
    public $created_at = null;
    public $created_by = null;
    public $updated_at = null;
    public $updated_by = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_info_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'customer_id', 'order_data_id', 'model', 'field_name', 'new_value', 'instrument', 'ip', 'comment'], 'required'],
            [['user_id', 'order_id', 'customer_id', 'order_data_id', 'instrument'], 'integer'],
            [['datetime'], 'safe'],
            [['model', 'field_name', 'city'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 48],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'customer_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_info_log_id' => Yii::t('app', 'Order Info Log ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'order_data_id' => Yii::t('app', 'Order Data ID'),
            'model' => Yii::t('app', 'Model'),
            'field_name' => Yii::t('app', 'Field Name'),
            'last_value' => Yii::t('app', 'Last Value'),
            'new_value' => Yii::t('app', 'New Value'),
            'instrument' => Yii::t('app', 'Instrument'),
            'ip' => Yii::t('app', 'Ip'),
            'city' => Yii::t('app', 'City'),
            'datetime' => Yii::t('app', 'Datetime'),
            'comment' => Yii::t('app', 'Comment'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['customer_id' => 'customer_id']);
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}