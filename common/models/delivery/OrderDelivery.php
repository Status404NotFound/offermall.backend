<?php

namespace common\models\delivery;

use common\models\finance\Currency;
use common\models\offer\Offer;
use common\models\order\Order;
use common\modules\user\models\tables\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_delivery".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $order_hash
 * @property integer $offer_id
 * @property integer $sent_by
 * @property integer $delivery_api_id
 * @property string $delivery_api_name
 * @property integer $user_api_id
 * @property string $tracking_no
 * @property string $shipment_no
 * @property string $remote_status
 * @property string $shipment_data
 * @property string $status_date
 * @property double $money_in_fact
 * @property integer $currency_id
 * @property string $delivery_date_in_fact
 * @property string $report_no
 *
 * @property Currency $currency
 * @property DeliveryApi $deliveryApi
 * @property Offer $offer
 * @property Order $order
 * @property User $sentBy
 * @property UserDeliveryApi $userApi
 */
class OrderDelivery extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_delivery';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'order_hash', 'offer_id', 'sent_by', 'currency_id'], 'required'],
            [['order_id', 'order_hash', 'offer_id', 'sent_by', 'delivery_api_id', 'user_api_id', 'currency_id'], 'integer'],
            [['status_date', 'delivery_date_in_fact'], 'safe'],
            [['money_in_fact'], 'number'],
            [['delivery_api_name', 'tracking_no', 'shipment_no', 'shipment_data', 'remote_status', 'report_no'], 'string', 'max' => 255],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
            [['delivery_api_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeliveryApi::className(), 'targetAttribute' => ['delivery_api_id' => 'delivery_api_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['sent_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sent_by' => 'id']],
            [['user_api_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDeliveryApi::className(), 'targetAttribute' => ['user_api_id' => 'api_id']],
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
            'sent_by' => Yii::t('app', 'Sent By'),
            'delivery_api_id' => Yii::t('app', 'Delivery Api ID'),
            'delivery_api_name' => Yii::t('app', 'Delivery Api Name'),
            'user_api_id' => Yii::t('app', 'User Api ID'),
            'tracking_no' => Yii::t('app', 'Tracking No'),
            'shipment_no' => Yii::t('app', 'Shipment No'),
            'remote_status' => Yii::t('app', 'Remote Status'),
            'shipment_data' => Yii::t('app', 'Shipment Data'),
            'status_date' => Yii::t('app', 'Status Date'),
            'money_in_fact' => Yii::t('app', 'Money In Fact'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'delivery_date_in_fact' => Yii::t('app', 'Delivery Date In Fact'),
            'report_no' => Yii::t('app', 'Report No'),
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
    public function getDeliveryApi()
    {
        return $this->hasOne(DeliveryApi::className(), ['delivery_api_id' => 'delivery_api_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getSentBy()
    {
        return $this->hasOne(User::className(), ['id' => 'sent_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserApi()
    {
        return $this->hasOne(UserDeliveryApi::className(), ['api_id' => 'user_api_id']);
    }
}