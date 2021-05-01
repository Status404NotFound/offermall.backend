<?php

namespace common\models\customer;

use common\helpers\FishHelper;
use common\models\BaseModel;
use common\models\geo\Countries;
use common\models\geo\GeoArea;
use common\models\geo\GeoCity;
use common\models\geo\GeoRegion;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\order\Order;
use common\services\log\orderInfo\OrderInfoLogService;
use Yii;

/**
 * This is the model class for table "customer".
 *
 * @property integer $customer_id
 * @property string $name
 * @property integer $country_id
 * @property integer $region_id
 * @property integer $city_id
 * @property integer $area_id
 * @property string $address
 * @property integer $phone_country_code
 * @property integer $phone
 * @property integer $phone_extension
 * @property integer $additional_phone
 * @property string $phone_string
 * @property string $email
 * @property string $pin
 * @property integer $customer_status
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property GeoCity $city
 * @property Countries $country
 * @property GeoRegion $region
 * @property CustomerSystem[] $customerSystems
 * @property Order[] $orders
 */
class Customer extends BaseModel
{
    public $created_by = null;
    public $instrument = false;

    const STATUS_NORMAL = 1;
    const STATUS_DENIED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'region_id', 'city_id', 'area_id', 'phone_country_code', 'phone', 'phone_extension', 'additional_phone', 'customer_status', 'updated_by'], 'integer'],
            [['phone'], 'required'],
            [['phone_string', 'description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'address', 'email', 'pin'], 'string', 'max' => 255],
//            [['phone', 'email'], 'unique', 'targetAttribute' => ['phone', 'email'], 'message' => 'The combination of Phone and Email has already been taken.'],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeoCity::className(), 'targetAttribute' => ['city_id' => 'city_id']],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Countries::className(), 'targetAttribute' => ['country_id' => 'id']],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeoRegion::className(), 'targetAttribute' => ['region_id' => 'region_id']],
            [['area_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeoArea::className(), 'targetAttribute' => ['area_id' => 'area_id']],
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        // TODO: Create Factory
        $oldModel = self::getModelByPk();
        if (isset($this->instrument) && $this->instrument == true)
            (new OrderInfoLogService())->logModel($this, $oldModel, false);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => Yii::t('app', 'Customer ID'),
            'name' => Yii::t('app', 'Name'),
            'country_id' => Yii::t('app', 'Country ID'),
            'region_id' => Yii::t('app', 'Region ID'),
            'city_id' => Yii::t('app', 'City ID'),
            'area_id' => Yii::t('app', 'Area ID'),
            'address' => Yii::t('app', 'Address'),
            'phone_country_code' => Yii::t('app', 'Phone Country Code'),
            'phone' => Yii::t('app', 'Phone'),
            'phone_extension' => Yii::t('app', 'Phone Extension'),
            'additional_phone' => Yii::t('app', 'Additional Phone'),
            'phone_string' => Yii::t('app', 'Phone String'),
            'email' => Yii::t('app', 'Email'),
            'pin' => Yii::t('app', 'Pin'),
            'customer_status' => Yii::t('app', 'Customer Status'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(GeoCity::className(), ['city_id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Countries::className(), ['id' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(GeoRegion::className(), ['region_id' => 'region_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArea()
    {
        return $this->hasOne(GeoArea::className(), ['area_id' => 'area_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerSystems()
    {
        return $this->hasMany(CustomerSystem::className(), ['customer_id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'customer_id']);
    }

    public static function getValidPhoneNumber($phone_number)
    {
        $new_phone = str_replace(array('+', ' '), '', $phone_number);
        return intval($new_phone);
    }

    /**
     * @return mixed
     */
    public function getInstrument()
    {
        return $this->instrument;
    }

    /**
     * @param $instrument
     * @return bool
     */
    public function setInstrument($instrument)
    {
        if (OrderInfoInstrument::findInstrument($instrument) && $this->instrument = $instrument) return true;
        return false;
    }

    public function getLastOrderId()
    {
        $orders = self::getOrders()->all();
        $last_date = null;
        $order_id = null;
        foreach ($orders as $order) {
            if ($order->created_at > $last_date) $order_id = $order->order_id;
        }
        return $order_id;
    }

    public function getLastOrder()
    {
        $orders = self::getOrders()->all();
        $last_date = null;
        $order_model = null;
        foreach ($orders as $order) {
            if ($order->created_at > $last_date) $order_model = $order;
        }
        return $order_model;
    }
}
