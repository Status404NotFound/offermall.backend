<?php

namespace common\models\customer;

use Yii;

/**
 * This is the model class for table "{{%old_crm_history}}".
 *
 * @property int $customer_id
 * @property string $name
 * @property string $phone
 * @property string $address
 * @property string $email
 * @property int $ip
 * @property string $advert_name
 * @property string $country_name
 * @property string $iso
 * @property string $offer_name
 * @property int $advert_id
 * @property int $status
 * @property string $created_at
 */
class OldHistory extends \yii\db\ActiveRecord
{
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    const NOT_VALID = 3;
    const NOT_VALID_APPROVED = 30;
    const VALID_APPROVED = 31;

    const WAITING_DELIVERY = 10;
    const DELIVERY_IN_PROGRESS = 11;
    const CANCEL_ORDER = 12;

    const NOT_PAID = 110;
    const SUCCESS_DELIVERY = 111;
    const RETURNED = 112;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%old_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'status', 'ip', 'advert_id'], 'integer'],
            [['name', 'address', 'email', 'advert_name', 'country_name', 'iso', 'offer_name', 'created_at'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => Yii::t('app', 'Customer ID'),
            'name' => Yii::t('app', 'Name'),
            'phone' => Yii::t('app', 'Phone'),
            'address' => Yii::t('app', 'Address'),
            'email' => Yii::t('app', 'Email'),
            'ip' => Yii::t('app', 'Ip'),
            'advert_name' => Yii::t('app', 'Advert Name'),
            'country_name' => Yii::t('app', 'Country Name'),
            'iso' => Yii::t('app', 'Iso'),
            'offer_name' => Yii::t('app', 'Offer Name'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return array
     */
    public static function oldCrmStatusLabels()
    {
        return [
            self::PENDING => Yii::t('app', 'Pending'),
            self::APPROVED => Yii::t('app', 'Approved'),
            self::REJECTED => Yii::t('app', 'Rejected'),

            self::WAITING_DELIVERY => Yii::t('app', 'Waiting for delivery'),
            self::DELIVERY_IN_PROGRESS => Yii::t('app', 'Delivery in progress'),

            self::CANCEL_ORDER => Yii::t('app', 'Order canceled'),
            self::NOT_PAID => Yii::t('app', 'Not paid'),
            self::SUCCESS_DELIVERY => Yii::t('app', 'Success delivery'),
            self::RETURNED => Yii::t('app', 'Returned'),
            self::NOT_VALID => Yii::t('app', 'Not valid'),
            self::NOT_VALID_APPROVED => Yii::t('app', 'Not valid Checked'),
            self::VALID_APPROVED => Yii::t('app', 'Valid'),
        ];
    }
}
