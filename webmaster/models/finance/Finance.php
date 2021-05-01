<?php


namespace webmaster\models\finance;

use common\models\order\Order;
use common\models\order\OrderStatus;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Finance extends ActiveRecord
{

    public static function tableName()
    {
        return '{{wm_finance}}';
    }

    public function rules()
    {
        return [
            ['order_id', 'unique'],
            [['order_id', 'order_status', 'target_status', 'hold', 'wm_id'], 'required'],
            [['order_id', 'order_status', 'target_status', 'hold', 'price', 'wm_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'order_status' => Yii::t('app', 'Order Status'),
            'target_status' => Yii::t('app', 'Target Status'),
            'hold' => Yii::t('app', 'Hold Days'),
            'hold_time' => Yii::t('app', 'Time when order hold'),
            'price' => Yii::t('app', 'Price'),
            'wm_id' => Yii::t('app', 'WM ID'),
            'payment_status' => Yii::t('app', 'Payment Status')
        ];
    }

    const approved = "(" .
    OrderStatus::WAITING_DELIVERY . ", " . OrderStatus::DELIVERY_IN_PROGRESS . ", " .
    OrderStatus::SUCCESS_DELIVERY . ")";

    const rejected = "(" .
    OrderStatus::RETURNED . ", " . OrderStatus::NOT_PAID . ", " .
    OrderStatus::CANCELED . ", " . OrderStatus::REJECTED . ")";

    public static function getCurrentBalance(): int
    {
        self::transferToBalance();

        $query = self::find()->where(['wm_id' => Yii::$app->user->identity->getId()])->andWhere(['payment_status' => 2])->all();

        $balance = 0;
        foreach ($query as $pay){
            $balance = $balance + $pay->price;
        }
        return $balance;
    }

    public static function transferToBalance()
    {
        $holdPayments = self::find()->where(['wm_id' => Yii::$app->user->identity->getId()])->andWhere(['payment_status' => 1])->all();
        foreach ($holdPayments as $holdPayment)
        {
            $endHold = new \DateTime($holdPayment->hold_time);
            $endHold->add(new \DateInterval('P'.$holdPayment->hold.'D'));
            if($endHold <= new \DateTime)
            {
                $holdPayment->payment_status = 2;
                $holdPayment->save();
            }
        }
    }

    public static function getHoldBalance(): int
    {
        $query = self::find()->where(['wm_id' => Yii::$app->user->identity->getId()])->andWhere(['payment_status' => 1])->all();

        $balance = 0;
        foreach ($query as $pay){
            $balance = $balance + $pay->price;
        }
        return $balance;
    }

    public static function getHoldPaymentOrders($order_id)
    {
        return Order::find()
            ->leftJoin('order_data', 'order_data.order_id = `order`.order_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->leftJoin('target_wm_group', '`target_wm_group`.target_wm_group_id = `target_wm`.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->where('`order`.order_status >= wm_offer_target.wm_offer_target_status')
            ->andWhere("wm_offer_target.wm_offer_target_status != ". OrderStatus::WAITING_DELIVERY ."")
            ->andWhere('`order`.order_id = '.$order_id)
            ->orWhere("wm_offer_target.wm_offer_target_status = ". OrderStatus::WAITING_DELIVERY ." AND `order`.order_status IN ". self::approved ."")
            ->andWhere('`order`.order_id = '.$order_id)
            ->andWhere(['`order`.deleted' => 0])->one();
    }

    public static function getRejectedOrders($order_id)
    {
        return Order::find()
            ->leftJoin('order_data', 'order_data.order_id = `order`.order_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->leftJoin('target_wm_group', '`target_wm_group`.target_wm_group_id = `target_wm`.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->where("wm_offer_target.wm_offer_target_status = ". OrderStatus::WAITING_DELIVERY ." AND `order`.order_status IN ". self::rejected ."")
            ->andWhere('`order`.order_id = '.$order_id)
            ->andWhere(['`order`.deleted' => 0])->one();
    }

    public static function getPaymentByOrderId($orderId)
    {
        return self::find()->where(['order_id' => $orderId])->one();
    }
}