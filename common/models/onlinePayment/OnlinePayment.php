<?php

namespace common\models\onlinePayment;

use \Yii;
use common\models\BaseModel;
use common\models\finance\Currency;
use common\models\offer\Offer;
use common\models\order\Order;

/**
 * This is the model class for table "online_payment".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $order_hash
 * @property integer $offer_id
 * @property double $amount
 * @property integer $currency_id
 * @property integer $currency_name
 * @property string $tracking_id
 * @property string $bank_ref_no
 * @property string $payment_name
 * @property string $payment_status
 * @property string $message
 * @property string $serialized_data
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Currency $currency
 * @property Offer $offer
 * @property Order $order
 */
class OnlinePayment extends BaseModel
{
    public $updated_by = null;
    public $created_by = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'order_hash', 'offer_id', 'currency_id', 'serialized_data'], 'required'],
            [['order_id', 'order_hash', 'offer_id', 'currency_id'], 'integer'],
            [['amount'], 'number'],
            [['message', 'serialized_data', 'currency_name'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['tracking_id', 'bank_ref_no', 'payment_name', 'payment_status'], 'string', 'max' => 255],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
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
            'order_hash' => Yii::t('app', 'Order Hash'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'amount' => Yii::t('app', 'Amount'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'currency_name' => Yii::t('app', 'Currency Name'),
            'tracking_id' => Yii::t('app', 'Tracking ID'),
            'bank_ref_no' => Yii::t('app', 'Bank Ref No'),
            'payment_name' => Yii::t('app', 'Payment Name'),
            'payment_status' => Yii::t('app', 'Payment Status'),
            'message' => Yii::t('app', 'Message'),
            'serialized_data' => Yii::t('app', 'Serialized Data'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['currency_id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }

    /**
     * @param $order_id
     * @return array|OnlinePayment|null|\yii\db\ActiveRecord
     */
    public static function OrderPayment($order_id)
    {
        return self::find()
            ->select([
                'online_payment.amount as payment_amount',
                'online_payment.payment_status',
                'online_payment.message as payment_message',
            ])
            ->where(['order_id' => $order_id])
            ->asArray()
            ->one();
    }
}