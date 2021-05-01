<?php

namespace common\models\customer;

use common\models\geo\Countries;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "customer_system".
 *
 * @property integer $customer_id
 * @property integer $order_id
 * @property string $ip
 * @property integer $country_id
 * @property string $os
 * @property string $browser
 * @property string $cookie
 * @property string $view_hash
 * @property string $sid
 *
 * @property Customer $customer
 * @property Countries $country
 */
class CustomerSystem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_system';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'customer_id'], 'integer'],
            [['ip', 'os', 'sid', 'view_hash'], 'string', 'max' => 32],
            [['browser'], 'string', 'max' => 255],
            [['cookie'], 'string', 'max' => 50],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'customer_id']],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Countries::className(), 'targetAttribute' => ['country_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_system_id' => Yii::t('app', 'Customer System ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'ip' => Yii::t('app', 'Ip'),
            'country_id' => Yii::t('app', 'Country ID'),
            'os' => Yii::t('app', 'Os'),
            'sid' => Yii::t('app', 'Session Id'),
            'view_hash' => Yii::t('app', 'View Hash'),
            'browser' => Yii::t('app', 'Browser'),
            'cookie' => Yii::t('app', 'Cookie'),
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
    public function getCountry()
    {
        return $this->hasOne(Countries::className(), ['id' => 'country_id']);
    }
}
