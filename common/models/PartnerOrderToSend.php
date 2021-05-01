<?php

namespace common\models;

use common\models\order\Order;
use Yii;

/**
 * This is the model class for table "partner_orders_to_send".
 *
 * @property integer $id
 * @property string $order_id
 * @property string $partner_id
 * @property string $iso
 *
 * @property PartnerCrm $partner
 * @property Order $order
 */
class PartnerOrderToSend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_orders_to_send';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'partner_id'], 'required'],
            [['order_id', 'partner_id'], 'integer'],
            ['iso', 'string'],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerCrm::className(), 'targetAttribute' => ['partner_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(PartnerCrm::className(), ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }
}
