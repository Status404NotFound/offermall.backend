<?php

namespace common\models\order;

use common\models\customer\CustomerView;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_view".
 *
 * @property integer $order_id
 * @property integer $order_hash
 * @property integer $order_status
 * @property integer $status_reason
 * @property string $created_at
 * @property string $delivery_date
 * @property integer $total_amount
 * @property double $total_cost
 * @property double $advert_commission
 * @property string $owner_name
 * @property integer $owner_id
 * @property integer $target_advert_id
 * @property integer $customer_id
 * @property string $customer_name
 * @property integer $advert_offer_target_id
 * @property integer $advert_offer_target_status
 * @property integer $country_id
 * @property string $iso
 * @property integer $currency_id
 * @property string $currency_name
 * @property string $country_name
 * @property integer $offer_id
 * @property string $offer_name
 * @property string $declaration
 * @property string $referrer
 * @property string $sub_id_1
 * @property string $sub_id_2
 * @property string $sub_id_3
 * @property string $sub_id_4
 * @property string $sub_id_5
 * @property string $view_time
 * @property string $view_hash
 * @property integer $flow_id
 * @property integer $wm_id
 * @property integer $flow_wm_id
 * @property string $flow_wm_name
 * @property string $wm_name
 * @property integer $deleted
 */
class OrderView extends ActiveRecord
{

    public static function primaryKey()
    {
        return ['order_id'];
//        return static::getTableSchema()->primaryKey;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'order_hash', 'order_status', 'status_reason', 'total_amount', 'owner_id', 'target_advert_id', 'customer_id', 'advert_offer_target_id', 'advert_offer_target_status', 'country_id', 'currency_id', 'offer_id', 'flow_id', 'wm_id', 'flow_wm_id', 'deleted', 'target_wm_id'], 'integer'],
            [['created_at', 'delivery_date'], 'safe'],
            [['total_cost', 'advert_commission'], 'number'],
            [['customer_id', 'offer_id'], 'required'],
//            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerView::className(), 'targetAttribute' => ['customer_id' => 'customer_id']],

            [['owner_name', 'customer_name', 'currency_name', 'country_name', 'offer_name', 'declaration', 'referrer', 'sub_id_1', 'sub_id_2', 'sub_id_3', 'sub_id_4', 'sub_id_5', 'view_time', 'view_hash', 'flow_wm_name', 'wm_name'], 'string', 'max' => 255],
            [['iso'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'order_hash' => Yii::t('app', 'Order Hash'),
            'order_status' => Yii::t('app', 'Order Status'),
            'status_reason' => Yii::t('app', 'Status Reason'),
            'created_at' => Yii::t('app', 'Created At'),
            'delivery_date' => Yii::t('app', 'Delivery Date'),
            'total_amount' => Yii::t('app', 'Total Amount'),
            'total_cost' => Yii::t('app', 'Total Cost'),
            'advert_commission' => Yii::t('app', 'Advert Commission'),
            'owner_name' => Yii::t('app', 'Owner Name'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'customer_name' => Yii::t('app', 'Customer Name'),
            'advert_offer_target_id' => Yii::t('app', 'Advert Offer Target ID'),
            'advert_offer_target_status' => Yii::t('app', 'Advert Offer Target Status'),
            'country_id' => Yii::t('app', 'Country ID'),
            'iso' => Yii::t('app', 'Iso'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'currency_name' => Yii::t('app', 'Currency Name'),
            'country_name' => Yii::t('app', 'Country Name'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'offer_name' => Yii::t('app', 'Offer Name'),
            'declaration' => Yii::t('app', 'Declaration'),
            'referrer' => Yii::t('app', 'Referrer'),
            'sub_id_1' => Yii::t('app', 'Sub Id 1'),
            'sub_id_2' => Yii::t('app', 'Sub Id 2'),
            'sub_id_3' => Yii::t('app', 'Sub Id 3'),
            'sub_id_4' => Yii::t('app', 'Sub Id 4'),
            'sub_id_5' => Yii::t('app', 'Sub Id 5'),
            'view_time' => Yii::t('app', 'View Time'),
            'view_hash' => Yii::t('app', 'View Hash'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'flow_wm_id' => Yii::t('app', 'Flow Wm ID'),
            'flow_wm_name' => Yii::t('app', 'Flow Wm Name'),
            'wm_name' => Yii::t('app', 'Wm Name'),
            'deleted' => Yii::t('app', 'Deleted'),
        ];
    }

//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getCustomer()
//    {
//        return $this->hasOne(CustomerView::className(), ['customer_id' => 'customer_id']);
//    }

//    /**
//     * @inheritdoc
//     * @return OrderViewQuery the active query used by this AR class.
//     */
//    public static function find()
//    {
//        return new OrderViewQuery(get_called_class());
//    }
}