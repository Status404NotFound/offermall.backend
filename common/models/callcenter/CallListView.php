<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "call_list_view".
 *
 * @property integer $order_id
 * @property string $time_to_call
 * @property integer $attempts
 * @property integer $lead_group
 * @property integer $language_id
 * @property integer $operator_id
 * @property integer $order_hash
 * @property integer $customer_id
 * @property integer $order_status
 * @property string $created_at
 * @property string $delivery_date
 * @property integer $total_amount
 * @property double $total_cost
 * @property double $advert_commission
 * @property integer $offer_id
 * @property string $owner_name
 * @property integer $owner_id
 * @property string $customer_name
 * @property integer $phone
 * @property integer $phone_country_code
 * @property integer $phone_extension
 * @property integer $country_id
 * @property integer $city_id
 * @property string $address
 * @property string $country_name
 * @property string $offer_name
 */
class CallListView extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_list_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'order_id',
                'attempts',
                'lead_group',
                'language_id',
                'operator_id',
                'order_hash',
                'customer_id',
                'order_status',
                'total_amount',
                'lead_status',
                'lead_state',
                'target_advert_id',
                'offer_id',
                'owner_id',
                'phone',
                'phone_country_code',
                'phone_extension',
                'country_id',
                'city_id',

                'is_enable_offer',
                'offer_operator_id',
                'operator_name',

                'language_operator_id',
                'is_enable_language',

                'geo_operator_id',
                'is_enable_geo',
                'paid_online',
                'info',

            ], 'integer'],

            [['time_to_call', 'created_at', 'delivery_date', 'call_list_updated_at'], 'safe'],
            [['total_cost', 'advert_commission'], 'number'],
            [['owner_name', 'customer_name', 'address', 'country_name', 'iso', 'offer_name', 'language_name', 'language_code', 'city_name', 'email', 'pin'], 'string', 'max' => 255],
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
            'lead_group' => Yii::t('app', 'Lead Group'),
            'language_id' => Yii::t('app', 'Language ID'),
            'operator_id' => Yii::t('app', 'Operator ID'),
            'order_hash' => Yii::t('app', 'Order Hash'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'order_status' => Yii::t('app', 'Order Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'delivery_date' => Yii::t('app', 'Delivery Date'),
            'total_amount' => Yii::t('app', 'Total Amount'),
            'total_cost' => Yii::t('app', 'Total Cost'),
            'advert_commission' => Yii::t('app', 'Advert Commission'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'owner_name' => Yii::t('app', 'Owner Name'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'customer_name' => Yii::t('app', 'Customer Name'),
            'phone' => Yii::t('app', 'Phone'),
            'phone_country_code' => Yii::t('app', 'Phone Country Code'),
            'phone_extension' => Yii::t('app', 'Phone Extension'),
            'country_id' => Yii::t('app', 'Country ID'),
            'city_id' => Yii::t('app', 'City ID'),
            'address' => Yii::t('app', 'Address'),
            'country_name' => Yii::t('app', 'Country Name'),
            'offer_name' => Yii::t('app', 'Offer Name'),
        ];
    }

    /**
     * @inheritdoc
     * @return CallListViewQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CallListViewQuery(get_called_class());
    }
}
