<?php

namespace common\models\order;

use common\helpers\FishHelper;
use common\models\BaseModel;
use common\models\log\orderSku\OrderSkuInstrument;
use common\models\product\ProductSku;
use common\modules\user\models\tables\User;
use common\services\log\orderInfo\OrderInfoLogService;
use common\services\log\orderSku\OrderSkuLogService;
use Yii;

/**
 * This is the model class for table "order_sku".
 *
 * @property integer $order_sku_id
 * @property integer $order_id
 * @property integer $sku_id
 * @property integer $amount
 * @property double $cost
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property User $createdBy
 * @property Order $order
 * @property ProductSku $sku
 * @property User $updatedBy
 */
class OrderSku extends BaseModel
{
    /**
     * @var $instrument
     */
    private $instrument = false;

    /**
     * @return mixed
     */
    public function beforeDelete()
    {
        if (!empty($this->instrument))
            (new OrderSkuLogService())->logModel($this, $this, true);
        return parent::beforeDelete();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $oldModel = self::findOne(['order_sku_id' => $this->order_sku_id]) ?? new self();
        if ($this->instrument == true && ($this->amount != $oldModel->amount))
            (new OrderSkuLogService())->logModel($this, $oldModel, false);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_sku';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'sku_id'], 'required'],
            [['order_id', 'sku_id', 'amount', 'created_by', 'updated_by'], 'integer'],
            ['cost', 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_id', 'sku_id'], 'unique', 'targetAttribute' => ['order_id', 'sku_id'], 'message' => 'The combination of Order ID and Sku ID has already been taken.'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductSku::className(), 'targetAttribute' => ['sku_id' => 'sku_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_sku_id' => Yii::t('app', 'Order Sku ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'sku_id' => Yii::t('app', 'Sku ID'),
            'amount' => Yii::t('app', 'Amount'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public static function getOrderTotalAmount($order_id)
    {
        return self::find()
            ->where(['order_id' => $order_id])
            ->sum('amount');
    }

    /**
     * @param $order_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findOrderSkuByOrderId($order_id)
    {
        return self::find()
            ->where(['order_id' => $order_id])
            ->indexBy('order_sku_id')
            ->all();
    }

    /**
     * @param $order_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findForOrderCommission($order_id)
    {
        return self::find()
            ->select(['sku_id', 'amount'])
            ->where(['order_id' => $order_id])
            ->indexBy('sku_id')
            ->asArray()
            ->all();
    }

    /**
     * @param $order_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findListByOrderId($order_id)
    {
        return self::find()
            ->select(['OS.sku_id', 'OS.amount', 'PS.sku_name'])
            ->from('order_sku OS')
            ->join('LEFT JOIN', 'product_sku PS', 'PS.sku_id = OS.sku_id')
            ->where(['OS.order_id' => $order_id])
            ->asArray()
            ->all();
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
        if (OrderSkuInstrument::findInstrument($instrument) && $this->instrument = $instrument) return true;
        return false;
    }
}