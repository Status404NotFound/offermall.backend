<?php

namespace common\models\delivery;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "delivery_api".
 *
 * @property integer $delivery_api_id
 * @property string $api_name
 * @property string $api_alias
 * @property string $description
 *
 * @property OrderDelivery[] $orderDeliveries
 * @property UserDeliveryApi[] $userDeliveryApis
 */
class DeliveryApi extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delivery_api';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['api_name', 'api_alias'], 'required'],
            [['description'], 'string'],
            [['api_name', 'api_alias'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'delivery_api_id' => Yii::t('app', 'Delivery Api ID'),
            'api_name' => Yii::t('app', 'Api Name'),
            'api_alias' => Yii::t('app', 'Api Alias'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDeliveries()
    {
        return $this->hasMany(OrderDelivery::className(), ['delivery_api_id' => 'delivery_api_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDeliveryApis()
    {
        return $this->hasMany(UserDeliveryApi::className(), ['delivery_api_id' => 'delivery_api_id']);
    }

    public static function getNameById($delivery_api_id)
    {
        $api = self::findOne(['delivery_api_id' => $delivery_api_id]);
        return $api->api_name;
    }
}