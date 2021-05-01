<?php
namespace common\services\order;

use common\models\customer\Customer;
use common\models\customer\CustomerSystem;
use common\models\geo\Geo;
use common\models\Instrument;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\wm\TargetWm;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\services\callcenter\call_list\CallRegistration;
use Yii;
use common\modules\user\models\tables\User;
use yii\helpers\ArrayHelper;

class RegOrderCustom
{
    public $order;
    public $order_data;

    public $customer;
    public $customer_system;

    public $data;

    public $result = true;


    public function init($data)
    {
        $this->data = $data;
        $this->createCustomer();
        $this->createOrder();
        $this->createCustomerSystem();
        $this->createOrderData();

        $call_config = [];
        if(Yii::$app->user->identity->role == User::ROLE_OPERATOR) $call_config['operator_id'] = Yii::$app->user->id;
        (new CallRegistration($this->order->order_id, $call_config));
    }

    public function getChangeGeoData($offer_id)
    {
        $aots = AdvertOfferTarget::find()
            ->select([
                'advert_offer_target.geo_id',
                'geo.geo_name',
                'geo.iso',
            ])
            ->join('LEFT JOIN', 'geo', 'geo.geo_id=advert_offer_target.geo_id')
            ->where(['advert_offer_target.offer_id' => $offer_id])
            ->andWhere(['advert_offer_target.active' => true])
            ->groupBy('advert_offer_target.geo_id')
            ->asArray()
            ->all();

        return $aots;
    }

    public function initChangeGeo($data)
    {
        $this->data = $data;
        $order = Order::findOne($data['order_id']);
        $order_data = $order->orderData;
        $customer = $order->customer;
        $customer_system = $customer->customerSystems[0];

        $this->data['phone'] = $customer->phone;


        $tx = Yii::$app->db->beginTransaction();
        try
        {
            $this->init($this->data);
            $this->duplicateOrderData($order, $order_data, $customer, $customer_system);
            (new OrderCommonService)->changeStatus($order, OrderStatus::REJECTED, ['reason_id' => 22]);

            $this->order->instrument = Instrument::LMC_DUPLICATE_WRONG_GEO;
            $comment = "<span style='color: #1279d1;'> duplicete on WRONG GEO reason from #" . $order->order_hash . " </span>";
            (new OrderCommonService())->saveComment($this->order, $comment);
            $tx->commit();

        }catch (\Exception $e)
        {
            $tx->rollBack();
            throw $e;
        }

    }

    private function duplicateOrderData(Order $order, $order_data, Customer $customer, CustomerSystem $customer_system)
    {
        $this->customer->address = $customer->address;
        $this->customer->name = $customer->name;
        $this->customer->phone_country_code = $customer->phone_country_code;
        $this->customer->phone_string = $customer->phone_string;
        $this->customer->email = $customer->email;
        $this->customer->customer_status = $customer->customer_status;


        $this->customer->save();

        if (!is_null($order->flow_id))
        {   $target_wm_data = $this->getWmTargetId($order_data->wm_id) ?? $this->getChepolliNoTargetWmId();
            $this->order->target_wm_id = $target_wm_data['target_wm_id'];
            $this->order_data->wm_id = $target_wm_data['wm_id'];
        }
//        else{
//            $target_wm_data = $this->getChepolliNoTargetWmId();
//            $this->order->target_wm_id = $target_wm_data['target_wm_id'];
//            $this->order_data->wm_id = $target_wm_data['wm_id'];
//        }


        $this->order->flow_id = $order->flow_id;
        $this->order->status_reason = $order->status_reason;
        $this->order->delivery_date = $order->delivery_date;
        $this->order->total_amount = $order->total_amount;
        $this->order->total_cost = $order->total_cost;
        $this->order->usd_total_cost = $order->usd_total_cost;
        $this->order->advert_commission = $order->advert_commission;
        $this->order->usd_advert_commission = $order->usd_advert_commission;
        $this->order->wm_commission = $order->wm_commission;
        $this->order->usd_wm_commission = $order->usd_wm_commission;
        $this->order->session_id = $order->session_id;
        $this->order->is_autolead = $order->is_autolead;
        (new OrderCommonService)->changeStatus($this->order, OrderStatus::PENDING);

        $this->customer_system->ip = $customer_system->ip;
        $this->customer_system->os = $customer_system->os;
        $this->customer_system->sid = $customer_system->sid;
        $this->customer_system->view_hash = $customer_system->view_hash;
        $this->customer_system->browser = $customer_system->browser;
        $this->customer_system->cookie = $customer_system->cookie;
        $this->customer_system->save();

        $this->order_data->owner_id = $this->getTargetAdvertId()['advert_id'];
        $this->order_data->fields = $order_data->fields;
        $this->order_data->view_time = $order_data->view_time;
        $this->order_data->view_hash = $order_data->view_hash;
        $this->order_data->referrer = $order_data->referrer;
        $this->order_data->sub_id_1 = $order_data->sub_id_1;
        $this->order_data->sub_id_2 = $order_data->sub_id_2;
        $this->order_data->sub_id_3 = $order_data->sub_id_3;
        $this->order_data->sub_id_4 = $order_data->sub_id_4;
        $this->order_data->sub_id_5 = $order_data->sub_id_5;
        $this->order_data->declaration = $order_data->declaration;
        $this->order_data->save();
    }

    private function createOrder()
    {
        $order = new Order();

        $order->offer_id = $this->data['offer_id'];
        $order->target_advert_id = $this->getTargetAdvertId()['target_advert_id'] ?? null;
//        $order->order_hash = $order->offer_id . 0 . time() . 0 . (!is_null($order->target_advert_id) ? $order->target_advert_id : 0);
        $order->order_hash = $order->offer_id . time();
        $order->customer_id = $this->customer->customer_id;
        $order->created_by = \Yii::$app->user->id;
        $order->target_wm_id = isset($this->getChepolliNoTargetWmId()['target_wm_id']) ? $this->getChepolliNoTargetWmId()['target_wm_id'] : null;


        (new OrderCommonService)->changeStatus($order, OrderStatus::PENDING);
        $this->order = $order;

        return true;
    }


    private function createOrderData()
    {
        $order_data = new OrderData();
        $order_data->order_id = $this->order->order_id;
        $order_data->order_hash = $this->order->order_hash;
        $order_data->offer_id = $this->order->offer_id;
        $order_data->owner_id = isset($this->data['advert_id']) ? intval($this->data['advert_id']) : null;
        $order_data->wm_id = isset($this->getChepolliNoTargetWmId()['wm_id']) ? $this->getChepolliNoTargetWmId()['wm_id'] : 7;

        if ($order_data->save())
        {
            $this->order_data = $order_data;
            return true;
        }

        return false;
    }


    private function createCustomer()
    {
        $customer = new Customer();
        $customer->name = $this->data['name'] ?? null;
        $customer->phone = $this->data['phone'];
        $customer->country_id = $this->data['geo_id'];
        $customer->address = isset($this->data['address']) ? $this->data['address'] : null;
        $customer->phone_country_code = Geo::findOne(['geo_id' => $this->data['geo_id']])->phone_code;
        $customer->customer_status = 1;

        if ($customer->save())
        {
            $this->customer = $customer;
            return true;
        }

        return false;
    }

    private function createCustomerSystem()
    {
        $customer_system = new CustomerSystem();
        $customer_system->customer_id = $this->customer->customer_id;
        $customer_system->country_id = $this->data['geo_id'];

        if ($customer_system->save())
        {
            $this->customer_system = $customer_system;
            return true;
        }

        return false;
    }

    private function getTargetAdvertId()
    {
        $t_a_id_query = TargetAdvert::find()
            ->select([
                'target_advert_id',
                'advert_id',
            ])
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id=target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id=advert_offer_target.advert_offer_target_id')
            ->where(['advert_offer_target.offer_id' => $this->data['offer_id']])
            ->andWhere(['advert_offer_target.geo_id' => $this->data['geo_id']])
            ->andWhere(['target_advert_group.active' => true])
            ->andWhere(['advert_offer_target.active' => true]);

        if (isset($this->data['advert_id'])) $t_a_id_query->andWhere(['target_advert.advert_id' => $this->data['advert_id']]);

        $t_a_id = $t_a_id_query->asArray()->one();

        return $t_a_id;
    }

    private function getWmTargetId($wm_id)
    {
        $t_w_id_query = TargetWm::find()
            ->select([
                'target_wm.target_wm_id',
                'wm_id'
            ])
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm.target_wm_group_id=target_wm_group.target_wm_group_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'wm_offer_target.wm_offer_target_id=target_wm_group.wm_offer_target_id')
            ->where(['wm_offer_target.geo_id' => $this->data['geo_id']])
            ->andWhere(['wm_offer_target.offer_id' => $this->data['offer_id']])
            ->andWhere(['target_wm.wm_id' => $wm_id])
            ->andWhere(['target_wm.active' => 1])
            ->andWhere(['wm_offer_target.active' => 1])
            ->asArray()
            ->one();

//        var_dump($t_w_id_query);exit;
        return !empty($t_w_id_query) ? $t_w_id_query : null;
    }

    private function getChepolliNoTargetWmId()
    {
        $chepolliNoWmId = User::getChepollyNo_WmId();
        $t_w_id_query = TargetWm::find()
            ->select([
                'target_wm.target_wm_id',
                'wm_id'
            ])
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm.target_wm_group_id=target_wm_group.target_wm_group_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'wm_offer_target.wm_offer_target_id=target_wm_group.wm_offer_target_id')
            ->where(['wm_offer_target.geo_id' => $this->data['geo_id']])
            ->andWhere(['wm_offer_target.offer_id' => $this->data['offer_id']])
            ->andWhere(['target_wm.wm_id' => $chepolliNoWmId])
            ->andWhere(['target_wm.active' => 1])
            ->andWhere(['wm_offer_target.active' => 1])
            ->asArray()
            ->one();
//        var_dump('CHEPOLINO');
//        var_dump($t_w_id_query);exit;

        return $t_w_id_query;
    }
}