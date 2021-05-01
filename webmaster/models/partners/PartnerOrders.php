<?php

namespace webmaster\models\partners;

use Yii;
use yii\db\ActiveRecord;

class PartnerOrders extends ActiveRecord
{
    const ORDER_PENDING_STATUS = 0; //в ожидании
    const ORDER_REJECT_STATUS = 1; //отклонинна
    const ORDER_ACCEPT_STATUS = 2; //принята

    public static function tableName()
    {
        return '{{partner_orders}}';
    }

    public function rules()
    {
        return [
            ['order_id', 'unique'],
            [['order_id', 'order_hash', 'partner_id', 'status'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'order_hash' => 'Order Hash',
            'partner_id' => 'Partner ID',
            'status' => 'Order status',
            'method' => 'method',
            'send_data' => 'Send Data',
            'crm_resp' => 'Response from crm'
        ];
    }

    public static function getAllOrders(): array
    {
        return self::find()->all();
    }

    public static function getPendingOrdersByPartnerId($partner_id): array
    {
        return self::find()->where(['status' => self::ORDER_PENDING_STATUS])->andWhere(['partner_id' => $partner_id])->all();
    }

    public static function getRejectOrdersByPartnerId($partner_id): array
    {
        return self::find()->where(['status' => self::ORDER_REJECT_STATUS])->andWhere(['partner_id' => $partner_id])->all();
    }

    public static function getAcceptOrdersByPartnerId($partner_id): array
    {
        return self::find()->where(['status' => self::ORDER_ACCEPT_STATUS])->andWhere(['partner_id' => $partner_id])->all();
    }
}