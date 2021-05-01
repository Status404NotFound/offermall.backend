<?php

namespace common\models\customer;

use common\models\BaseModel;
use common\models\order\Order;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "customer_view".
 *
 * @property integer $customer_id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property integer $customer_status
 * @property string $description
 * @property string $ip
 * @property string $os
 * @property string $browser
 * @property string $sid
 * @property string $view_hash
 * @property string $cookie
 * @property integer $geo_id
 * @property string $geo_name
 * @property integer $city_id
 * @property string $city_name
 * @property string $address
 */
class CustomerView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'customer_status', 'geo_id'], 'integer'],
            [['description'], 'string'],
            [['geo_id', 'geo_name'], 'required'],
            [['name', 'email', 'browser', 'geo_name', 'country_name'], 'string', 'max' => 255],
            [['phone', 'ip', 'os', 'sid', 'view_hash'], 'string', 'max' => 32],
            [['cookie'], 'string', 'max' => 50],
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
            'email' => Yii::t('app', 'Email'),
            'customer_status' => Yii::t('app', 'Customer Status'),
            'description' => Yii::t('app', 'Description'),
            'ip' => Yii::t('app', 'Ip'),
            'os' => Yii::t('app', 'Os'),
            'browser' => Yii::t('app', 'Browser'),
            'sid' => Yii::t('app', 'Sid'),
            'view_hash' => Yii::t('app', 'View Hash'),
            'cookie' => Yii::t('app', 'Cookie'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'geo_name' => Yii::t('app', 'Geo Name'),
        ];
    }
}