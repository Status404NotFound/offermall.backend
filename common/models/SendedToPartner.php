<?php

namespace common\models;

use common\models\order\Order;
use common\modules\user\models\tables\User;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "partner_crm".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $partner_id
 * @property string $remote_order_id
 * @property integer $created_by
 * @property string $created_at
 *
 * @property PartnerCrm $partner
 * @property Order $order
 * @property User $sender
 */
class SendedToPartner extends ActiveRecord
{
    public $updated_at = null;
    public $updated_by = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sended_to_partner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'partner_id', 'created_by'], 'integer'],
            [['created_at', 'remote_order_id'], 'string'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'order_id' => 'Order ID',
            'remote_order_id' => 'Remote Order ID',
            'partner_id' => 'Partner ID',
            'created_by' => 'Created By',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
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
