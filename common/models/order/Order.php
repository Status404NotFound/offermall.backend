<?php

namespace common\models\order;

use common\models\BaseModel;
use common\models\callcenter\CallList;
use common\models\callcenter\OperatorPcs;
use common\models\customer\Customer;
use common\models\delivery\OrderDelivery;
use common\models\finance\Currency;
use common\models\flow\Flow;
use common\models\landing\OfferGeoPrice;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\log\orderInfo\OrderInfoLog;
use common\models\log\orderSku\OrderSkuLog;
use common\models\offer\Offer;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertGroupRules;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\targets\wm\TargetWmGroupRules;
use common\models\onlinePayment\OnlinePayment;
use common\models\product\ProductSku;
use common\models\stock\StockTraffic;
use common\models\TurboSmsOrder;
use common\services\log\orderInfo\OrderInfoLogService;
use Yii;

/**
 * This is the model class for table "order".
 *
 * @property integer $order_id
 * @property integer $order_hash
 * @property integer $offer_id
 * @property integer $target_advert_id
 * @property integer $target_wm_id
 * @property integer $flow_id
 * @property integer $customer_id
 * @property integer $order_status
 * @property integer $status_reason
 * @property string $delivery_date
 * @property integer $total_amount
 * @property double $total_cost
 * @property double $usd_total_cost
 * @property double $advert_commission
 * @property double $usd_advert_commission
 * @property double $wm_commission
 * @property double $usd_wm_commission
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $session_id
 * @property integer $is_autolead
 * @property string $created_at
 * @property string $updated_at
 * @property string $comment
 * @property integer $deleted
 * @property integer $bitrix_flag
 * @property integer $paid_online
 * @property string $information
 *
 * @property CallList $callList
 * @property DeclarationPrint[] $declarationPrints
 * @property OnlinePayment $onlinePayment
 * @property OperatorPcs[] $operatorPcs
 * @property Customer $customer
 * @property Flow $flow
 * @property Offer $offer
 * @property TargetAdvert $targetAdvert
 * @property TargetWm $targetWm
 * @property OrderData $orderData
 * @property OrderDelivery[] $orderDeliveries
 * @property OrderInfoLog[] $orderInfoLogs
 * @property OrderSku[] $orderSku
 * @property ProductSku[] $sku
 * @property OrderSkuLog[] $orderSkuLogs
 * @property StockTraffic[] $stockTraffics
 * @property TurboSmsOrder[] $turboSmsOrders
 */
class Order extends BaseModel
{
    public $instrument = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }

    public function rules()
    {
        return [
            [['order_id', 'order_hash', 'offer_id', 'target_advert_id', 'target_wm_id', 'flow_id', 'customer_id', 'order_status', 'status_reason', 'total_amount', 'created_by', 'updated_by', 'is_autolead'], 'integer'],
            [['offer_id', 'customer_id'], 'required'],
            [['delivery_date', 'created_at', 'updated_at'], 'safe'],
            [['total_cost', 'usd_total_cost', 'advert_commission', 'usd_advert_commission', 'wm_commission', 'usd_wm_commission'], 'number'],
            [['comment'], 'string'],
            [['order_hash'], 'unique'],
            [['session_id'], 'string', 'max' => 32],
            [['information'], 'string', 'max' => 255],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'customer_id']],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['target_advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetAdvert::className(), 'targetAttribute' => ['target_advert_id' => 'target_advert_id']],
            [['target_wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetWm::className(), 'targetAttribute' => ['target_wm_id' => 'target_wm_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'order_hash' => Yii::t('app', 'Order Hash'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'target_wm_id' => Yii::t('app', 'Target Wm ID'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'order_status' => Yii::t('app', 'Order Status'),
            'status_reason' => Yii::t('app', 'Status Reason'),
            'delivery_date' => Yii::t('app', 'Delivery Date'),
            'total_amount' => Yii::t('app', 'Total Amount'),
            'total_cost' => Yii::t('app', 'Total Cost'),
            'usd_total_cost' => Yii::t('app', 'Usd Total Cost'),
            'advert_commission' => Yii::t('app', 'Advert Commission'),
            'usd_advert_commission' => Yii::t('app', 'Usd Advert Commission'),
            'wm_commission' => Yii::t('app', 'Wm Commission'),
            'usd_wm_commission' => Yii::t('app', 'Usd Wm Commission'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'session_id' => Yii::t('app', 'Session ID'),
            'is_autolead' => Yii::t('app', 'Is Autolead'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'comment' => Yii::t('app', 'Comment'),
            'deleted' => Yii::t('app', 'Deleted'),
            'bitrix_flag' => Yii::t('app', '1C'),
            'paid_online' => Yii::t('app', 'Paid Online'),
            'information' => Yii::t('app', 'Information'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getCallList()
    {
        return $this->hasOne(CallList::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeclarationPrints()
    {
        return $this->hasMany(DeclarationPrint::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOnlinePayment()
    {
        return $this->hasOne(OnlinePayment::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperatorPcs()
    {
        return $this->hasMany(OperatorPcs::className(), ['order_id' => 'order_id']);
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
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvert()
    {
        return $this->hasOne(TargetAdvert::className(), ['target_advert_id' => 'target_advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetWm()
    {
        return $this->hasOne(TargetWm::className(), ['target_wm_id' => 'target_wm_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderData()
    {
        return $this->hasOne(OrderData::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderSku()
    {
        return $this->hasMany(OrderSku::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderInfoLogs()
    {
        return $this->hasMany(OrderInfoLog::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSku()
    {
        return $this->hasMany(ProductSku::className(), ['sku_id' => 'sku_id'])->viaTable('order_sku', ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockTraffics()
    {
        return $this->hasMany(StockTraffic::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDeliveries()
    {
        return $this->hasMany(OrderDelivery::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTurboSmsOrders()
    {
        return $this->hasMany(TurboSmsOrder::className(), ['order_id' => 'order_id']);
    }

    /**
     * @inheritdoc
     * @return OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function canBeChanged()
    {
        if ($this->order_status >= $this->targetAdvert->targetAdvertGroup->advertOfferTarget->advert_offer_target_status) {
            return false;
        }
        return true;
    }

    public function getAdvertTargetStatus()
    {
        return $this->targetAdvert->targetAdvertGroup->advertOfferTarget->advert_offer_target_status;
    }

    public function getWmTargetStatus()
    {
        return $this->targetWm->targetWmGroup->wmOfferTarget->wm_offer_target_status;
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

    public static function getAdvertRules($order_hash)
    {
        if (!$order = self::find()
            ->where(['order_hash' => $order_hash])
            ->andWhere(['IS NOT', 'target_advert_id', null])
            ->andWhere(['deleted' => 0])
            ->one()
        )
            return false;
        $rules = [
            'base_commission' => $order->targetAdvert->targetAdvertGroup->base_commission ?? null,
            'exceeded_commission' => $order->targetAdvert->targetAdvertGroup->exceeded_commission ?? null,
            'use_commission_rules' => $order->targetAdvert->targetAdvertGroup->use_commission_rules,
            'rules_by_pcs' => TargetAdvertGroupRules::getAdvertGroupRulesByGroupId($order->targetAdvert->target_advert_group_id)
        ];
        return $rules;
    }

    public static function getWmRules($order_hash)
    {
        if (!$order = self::find()
            ->where(['order_hash' => $order_hash])
            ->andWhere(['IS NOT', 'target_wm_id', null])
            ->andWhere(['deleted' => 0])
            ->one()
        ) return false;
        $rules = [
            'base_commission' => $order->targetWm->targetWmGroup->base_commission ?? null,
            'exceeded_commission' => $order->targetWm->targetWmGroup->exceeded_commission ?? null,
            'use_commission_rules' => $order->targetWm->targetWmGroup->use_commission_rules,
            'rules_by_pcs' => TargetWmGroupRules::getWmGroupRulesByGroupId($order->targetWm->target_wm_group_id),
        ];
        return $rules;
    }

    public static function getOrderSkuRules($order_id)
    {
        $order = self::findOne($order_id);
        $currency = Currency::findOne(['country_id' => $order->customer->country_id])->currency_name;
        $skus = TargetAdvertSku::find()
            ->select([
                'product_sku.sku_alias',
                'target_advert_sku.*',
            ])
            ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id=target_advert_sku.sku_id')
            ->where(['target_advert_sku.target_advert_id' => $order->target_advert_id])
            ->andWhere(['product_sku.active' => true])
            ->asArray()
            ->all();
        $result = [];
        foreach ($skus as $sku) {
            $result[$sku['target_advert_sku_id']] = [
                'sku_alias' => $sku['sku_alias']
            ];
            if ($sku['use_sku_cost_rules']) {
                $TASrules = TargetAdvertSkuRules::find()->where(['target_advert_sku_id' => $sku['target_advert_sku_id']])->asArray()->all();
                $rules = [];
                foreach ($TASrules as $rule) {
                    $rules[] = [
                        'amount' => $rule['amount'],
                        'cost' => $rule['cost'],
                        'currency' => $currency,
                    ];
                }
                $rules[] = [
                    'amount' => 'exceeded_cost',
                    'cost' => $sku['exceeded_cost'],
                    'currency' => $currency,
                ];
            } else {
                $rules = [];
                $rules[] = [
                    'amount' => 'base_cost',
                    'cost' => $sku['base_cost'],
                    'currency' => $currency,
                ];
            }

            $result[$sku['target_advert_sku_id']] += ['rules' => $rules];
        }

        return $result;

    }

    public static function getOrderCurrencyId($order_id)
    {
        $order = self::findOne(['order_id' => $order_id]);
        return $order->targetAdvert->targetAdvertGroup->currency_id;
    }

    public function getBaseItemCost()
    {
        $offerGeoPrice = OfferGeoPrice::find()
            ->select('new_price')
            ->where(['offer_id' => $this->offer_id]);

        if (isset($this->targetAdvert))
            $offerGeoPrice->andWhere(['geo_id' => $this->targetAdvert->targetAdvertGroup->advertOfferTarget->geo_id,]);
        $result = $offerGeoPrice->asArray()->one();
        return $result['new_price'];
    }
}