<?php

namespace common\models\log\orderSku;

use common\models\BaseModel;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\product\ProductSku;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "order_sku_log".
 *
 * @property integer $id
 * @property integer $order_sku_id
 * @property integer $order_id
 * @property integer $sku_id
 * @property integer $last_amount
 * @property integer $new_amount
 * @property integer $instrument
 * @property integer $delete
 * @property integer $user_id
 * @property string $ip
 * @property string $city
 * @property string $datetime
 * @property string $comment
 *
 * @property Order $order
 * @property ProductSku $sku
 * @property User $user
 */
class OrderSkuLog extends BaseModel
{
    public $created_at = false;
    public $created_by = false;
    public $updated_at = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_sku_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_sku_id', 'order_id', 'sku_id', 'instrument', 'ip', 'comment'], 'required'],
            [['order_sku_id', 'order_id', 'sku_id', 'last_amount', 'new_amount', 'instrument', 'delete', 'user_id'], 'integer'],
            [['datetime', 'comment'], 'safe'],
            [['ip'], 'string', 'max' => 48],
            [['city'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductSku::className(), 'targetAttribute' => ['sku_id' => 'sku_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_sku_id' => Yii::t('app', 'Order Sku ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'sku_id' => Yii::t('app', 'Sku ID'),
            'last_amount' => Yii::t('app', 'Last Amount'),
            'new_amount' => Yii::t('app', 'New Amount'),
            'instrument' => Yii::t('app', 'Instrument'),
            'delete' => Yii::t('app', 'Delete'),
            'user_id' => Yii::t('app', 'User ID'),
            'ip' => Yii::t('app', 'Ip'),
            'city' => Yii::t('app', 'City'),
            'datetime' => Yii::t('app', 'Datetime'),
            'comment' => Yii::t('app', 'Comment'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSku()
    {
        return $this->hasOne(ProductSku::className(), ['sku_id' => 'sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}