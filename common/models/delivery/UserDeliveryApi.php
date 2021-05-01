<?php

namespace common\models\delivery;

use common\helpers\FishHelper;
use common\models\geo\Countries;
use common\models\geo\Geo;
use common\modules\user\models\Permission;
use common\modules\user\models\tables\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_delivery_api".
 *
 * @property integer $api_id
 * @property string $api_name
 * @property integer $delivery_api_id
 * @property integer $permission_api_id
 * @property string $credentials
 * @property integer $country_id
 *
 * @property OrderDelivery[] $orderDeliveries
 * @property DeliveryApi $deliveryApi
 * @property Countries $country
 * @property User $advert
 */
class UserDeliveryApi extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_delivery_api';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delivery_api_id', 'country_id', 'credentials', 'permission_api_id'], 'required'],
            [['delivery_api_id', 'country_id', 'permission_api_id'], 'integer'],
            [['api_name', 'credentials'], 'string', 'max' => 255],
            //[['permission_api_id'], 'in', 'range' => [Permission::$available_apis]], // NOTE Don't forget to add Permission for new API wrapper
            [['delivery_api_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeliveryApi::className(), 'targetAttribute' => ['delivery_api_id' => 'delivery_api_id']],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Countries::className(), 'targetAttribute' => ['country_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'api_id' => Yii::t('app', 'Api ID'),
            'api_name' => Yii::t('app', 'Api Name'),
            'delivery_api_id' => Yii::t('app', 'Delivery Api ID'),
            'permission_api_id' => Yii::t('app', 'Permission API ID'),
            'credentials' => Yii::t('app', 'Credentials'),
            'country_id' => Yii::t('app', 'Country ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDeliveries()
    {
        return $this->hasMany(OrderDelivery::className(), ['user_api_id' => 'api_id']);
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
    public function getCountry()
    {
        return $this->hasOne(Countries::className(), ['id' => 'country_id']);
    }
}