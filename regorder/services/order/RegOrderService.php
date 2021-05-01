<?php

namespace regorder\services\order;

use common\models\customer\Customer;
use common\models\customer\CustomerSystem;
use common\models\flow\Flow;
use common\models\offer\Offer;
use common\models\offer\targets\advert\TargetAdvertView;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\models\TurboSmsOrder;
use common\modules\user\models\tables\User;
use common\services\callcenter\call_list\CallRegistration;
use common\services\customer\CustomerBlackListService;
use common\services\GeoService;
use common\services\order\OrderCommonService;
use common\services\ValidateException;
use common\services\webmaster\DomainParkingSrv;
use DateTime;
use DateTimeZone;
use regorder\services\customer\CustomerSystemService;
use regorder\services\order\exceptions\OrderAdvertServiceException;
use regorder\services\order\exceptions\RegOrderException;
use webmaster\models\form\Form;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;

/**
 * @property $attributes[]
 * @property CustomerSystemService $customerSystemService
 */
class RegOrderService
{
    public const NOT_AVAILABLE_TIME_FOR_SEND_SMS_START = '9:00 am';
    public const NOT_AVAILABLE_TIME_FOR_SEND_SMS_END = '9:00 pm';
    public const TIME_ZONE_KIEV = 'Europe/Kiev';
    /**
     * @var
     */
    public $geo_id;
    public $in_blacklist;
    private $attributes;
    private $customerSystemService;
    private $geoSrv;

    // Not accessible time to send notification
    private $referrer;
    private $wm_id;
    private $flow;

    public function regOrder($attributes): Order
    {
        $this->attributes = $attributes;

        $this->referrer = new ReferrerParser($this->attributes['referrer']);
        if (empty($this->attributes) || !isset($this->attributes['phone'])) throw new RegOrderException('Empty request attributes or customer phone');
        $this->customerSystemService = new CustomerSystemService($this->attributes['userAgent']);
        $this->geoSrv = new GeoService($this->attributes['userIP']);
        $this->flow = $this->getFlow();
        $this->wm_id = $this->flow->wm_id;
        $this->geo_id = $this->getCountryId();

        $tx = Yii::$app->db->beginTransaction();
        try {
            $order = Order::findOne(['session_id' => $this->attributes['sid']]) ?? new Order();
            $customer = $this->saveCustomer($order ?? null);
            $this->saveCustomerSystem($customer->customer_id);
            $order = $this->saveOrder($customer->customer_id, $order ?? null);
            $this->saveOrderData($order);

            new CallRegistration($order->order_id, []);
            $tx->commit();
        } catch (InvalidParamException $e) {
            unset($order);
            $tx->rollBack();
            throw $e;
        } catch (ValidateException $e) {
            unset($order);
            $tx->rollBack();
            throw $e;
        } catch (Exception $e) {
            unset($order);
            $tx->rollBack();
            throw $e;
        }

        try {
            $this->checkCustomerInBlackList($order);
        } catch (\Exception $e) {

        }

        try {
            $this->sendNotification($order);
        } catch (\Exception $e) {

        }

        return $order;
    }

    private function getFlow(): ?Flow
    {
        $parts = parse_url($this->attributes['referrer']);
        if (isset($parts['host']) && $parkingDomain = (new DomainParkingSrv($parts['host']))->getParkingDomain()) {
            return $parkingDomain->flow;
        } elseif (isset($this->referrer->flow)) {
            return $this->referrer->flow;
        } elseif ($form = Form::getFormByUrl($parts["scheme"] . '://' . $parts['host'])) {
            return Flow::findOne($form->flow_id);
        } else {
            $flow = new Flow();
            $ChePollyNo_wm = User::getChepollyNo_WmId();
            $flow->wm_id = $ChePollyNo_wm->id;
            $flow->flow_id = null;
        }

        return $flow;
    }

    private function getCountryId(): int
    {
        $parts = parse_url($this->attributes['referrer']);
        if (isset($parts['host'])) {
            $domainParkingSrv = new DomainParkingSrv($parts['host']);
            if ($parkingDomain = $domainParkingSrv->getParkingDomain()) {
                if (!is_null($parkingDomain->geo_id)) return $parkingDomain->geo_id;
            }
        }

        if (isset($this->referrer->referrer['params']['geo']))
            return $this->geoSrv->getCountryIdByCountryISO($this->referrer->referrer['params']['geo']);
        return $this->geoSrv->geo_id;
    }

    private function saveCustomer($order = null): ?Customer
    {
        $customer = ($order !== null && !empty($order->customer_id)) ?
            Customer::findOne(['customer_id' => $order->customer_id]) :
            new Customer();
//            $customer = Customer::findOne(['phone' => $this->getValidPhoneNumber($this->attributes['phone'])]);
//            if (isset($customer) && $customer->phone_country_code != $this->customerSystemService->getCountryPhoneCode()) {
//                $customer = new Customer();
//            }
//            if (!isset($customer) && !$customer = Customer::find()
//                    ->where(['LIKE', 'phone_string', [$this->getValidPhoneNumber($this->attributes['phone'])]])
//                    ->one()) {
//                $customer = new Customer();
//            }


        $customer->setAttributes([
            'name' => $this->attributes['name'],
            'phone' => $this->getValidPhoneNumber($this->attributes['phone']),
            'phone_country_code' => $this->geoSrv->phone_code,
            'phone_string' => $customer->phone_string . ', ' . $this->getValidPhoneNumber($this->attributes['phone']),
            'country_id' => $this->geo_id,
            'city_id' => isset($this->attributes['city_id']) ? $this->attributes['city_id'] : null,
            'address' => isset($this->attributes['address']) ? $this->attributes['address'] : null,
            'email' => isset($this->attributes['email']) ? $this->attributes['email'] : null,
        ]);
        if (!$customer->save()) throw new ValidateException($customer->errors);
        return $customer;
    }

    private function getValidPhoneNumber($phone_number): int
    {
        $new_phone = str_replace(array('+', ' '), '', $phone_number);
        return intval($new_phone);
    }

    private function saveCustomerSystem($customer_id): bool
    {
        $customerSystem = CustomerSystem::findOne(['customer_id' => $customer_id]) ?? new CustomerSystem();
        $customerSystem->setAttributes([
            'customer_id' => $customer_id,
            'ip' => $this->attributes['userIP'],
            'country_id' => $this->geoSrv->geo_id,
            'os' => $this->customerSystemService->getOS(),
            'browser' => $this->customerSystemService->getBrowser(),
            'cookie' => $this->attributes['cookie'],
            'sid' => $this->attributes['sid'],
            'view_hash' => $this->attributes['view_hash'],
        ]);
        if (!$customerSystem->save()) throw new ValidateException($customerSystem->errors);
        return true;
    }

    private function saveOrder($customer_id, $order = null): Order
    {
        $order = ($order !== null) ? $order : new Order();
        if (!$offer_id = $this->getOfferId()) throw new RegOrderException('Offer Exists.');
//        $flow_id = isset($this->referrer->flow) ? $this->referrer->flow->flow_id : null;
        $order->setAttributes([
            'offer_id' => $offer_id,
            'customer_id' => $customer_id,
            'flow_id' => $this->flow->flow_id,
            'session_id' => $this->attributes['sid'],
            'is_autolead' => $order->isNewRecord ? 0 : 1,
        ]);
        $tx2 = Yii::$app->db->beginTransaction();
        try {
//            $advert_offer_target_status = (isset($this->referrer->flow->advert_offer_target_status)) ?
//                $this->referrer->flow->advert_offer_target_status : null; // TODO:: Think about getting $advert_offer_target_status from offer

            $advert_offer_target_status = (isset($this->flow->advert_offer_target_status)) ?
                $this->flow->advert_offer_target_status : null; // TODO:: Think about getting $advert_offer_target_status from offer
//            if (isset($this->referrer->flow->wm_id)) {
//                $this->wm_id = $this->referrer->flow->wm_id;
//            } else {
//                $ChePollyNo_wm = User::getChepollyNo_WmId();
//                $this->wm_id = $ChePollyNo_wm->id;
//            }

            $order->target_wm_id = (new OrderWmService($order, $this->geo_id))->getTargetWmId($advert_offer_target_status, $this->wm_id);
//            $advert_geo = Offer::getAdvertGeo($offer_id);
            $wm_geo = Offer::getWmGeo($offer_id);

//            $order->target_advert_id = ($this->geo_id == $order->targetWm->targetWmGroup->wmOfferTarget->geo_id)
            $order->target_advert_id = in_array($this->geo_id, $wm_geo)
                ? (new OrderAdvertService($order, $this->geo_id, $advert_offer_target_status))->getTargetAdvertId()
                : null;
            $tx2->commit();
        } catch (OrderAdvertServiceException $e) {
            $tx2->rollBack();
            throw $e;
        }

        $order_hash = $order->offer_id . 0 . time();
//            . 0 . (($order->target_advert_id !== null) ? $order->targetAdvert->advert_id : 0);

        $order->order_hash = $order->isNewRecord ? $order_hash : $order->order_hash;

        /** Find orders with current order_hash */
        $double = Order::findOne(['order_hash' => $order_hash]);
        if ($double) {
            $clone = new Order();
            $clone->attributes = $order->attributes;
            $clone->comment = 'Double of ' . $order->order_hash;
            $clone->order_hash = (integer)($order->offer_id . '00' . (time() + 1));
//                . '00' . (($order->target_advert_id !== null) ? $order->targetAdvert->advert_id : 0));
            (new OrderCommonService)->changeStatus($clone, OrderStatus::NOT_VALID, ['reason_id' => 2]); // Duplicate order
            $cloneOrder = $clone;
        }

        /** $order->save() in changeStatus() method */
        if ($order->target_advert_id == null) {
            (new OrderCommonService)->changeStatus($order, OrderStatus::NOT_VALID, ['reason_id' => 20]);
        } else {
            (new OrderCommonService)->changeStatus($order, OrderStatus::PENDING);
        }
        return $cloneOrder ?? $order;
    }

    private function getOfferId(): int
    {
        if ($offer = Offer::findOne(['offer_hash' => $this->attributes['offer_hash']])) {
            return $offer->offer_id;
        } elseif (!empty($this->flow) && isset($this->flow->offer_id)) {
            return $this->flow->offer_id;
        }
    }

//    private function getFlow()
//    {
//        $parts = parse_url($this->attributes['referrer']);
//        if (isset($parts['host']) && $parkingDomain = (new DomainParkingSrv($parts['host']))->getParkingDomain()) {
//            return $parkingDomain->flow;
//        } elseif (isset($this->referrer->flow)) {
//            return $this->referrer->flow;
//        } else {
//            $flow = new Flow();
//            $ChePollyNo_wm = User::getChepollyNo_WmId();
//            $flow->wm_id = $ChePollyNo_wm->id;
//            $flow->flow_id = null;
//        }
//
//        return $flow;
//    }

    private function saveOrderData(Order $order): bool
    {
        $orderData = OrderData::findOne(['order_id' => $order->order_id]) ?? new OrderData();
        $orderData->order_id = $order->order_id;
        $orderData->order_hash = $order->order_hash;
        $orderData->owner_id = ($order->target_advert_id !== null) ? $order->targetAdvert->advert_id : null;
        $orderData->wm_id = $this->wm_id;
        $orderData->offer_id = $order->offer_id;
        $orderData->referrer = $this->attributes['referrer'];

        if (isset($this->referrer->referrer['params']['sub_id_1'])) $orderData->sub_id_1 = $this->referrer->referrer['params']['sub_id_1'];
        if (isset($this->referrer->referrer['params']['sub_id_2'])) $orderData->sub_id_2 = $this->referrer->referrer['params']['sub_id_2'];
        if (isset($this->referrer->referrer['params']['sub_id_3'])) $orderData->sub_id_3 = $this->referrer->referrer['params']['sub_id_3'];
        if (isset($this->referrer->referrer['params']['sub_id_4'])) $orderData->sub_id_4 = $this->referrer->referrer['params']['sub_id_4'];
        if (isset($this->referrer->referrer['params']['sub_id_5'])) $orderData->sub_id_5 = $this->referrer->referrer['params']['sub_id_5'];

        $orderData->view_hash = $this->attributes['view_hash'];
        $orderData->view_time = $this->attributes['view_time'];
        $orderData->fields .= serialize($this->attributes);
        if (!$orderData->save()) throw new ValidateException($orderData->errors);
        return true;
    }

    /**
     * @param Order $order
     * @return bool
     * @throws Exception
     * @throws ValidateException
     */
    private function checkCustomerInBlackList(Order $order): bool
    {
        $in_blacklist = false;
        $blacklistService = new CustomerBlackListService();
        $customer_phone = isset($this->attributes['phone']) ? $this->getValidPhoneNumber($this->attributes['phone']) : '';
        $customer_email = isset($this->attributes['email']) ? $this->attributes['email'] : '';

        $ip = $blacklistService->checkCustomerIp($this->attributes['userIP']);
        $phone = $blacklistService->checkCustomerInformation('phone', $customer_phone);
        $email = $blacklistService->checkCustomerInformation('email', $customer_email);

        if (!empty($ip['block'])) {
            $blacklistService->saveAttempts($ip);
            (new OrderCommonService)->changeStatus($order, $ip['status_id'], ['reason_id' => $ip['reason_id']]);
            $in_blacklist = true;
        }

        if (!empty($phone['block'])) {
            $blacklistService->saveAttempts($phone);
            (new OrderCommonService)->changeStatus($order, $phone['status_id'], ['reason_id' => $phone['reason_id']]);
            $in_blacklist = true;
        }

        if (!empty($email['block'])) {
            $blacklistService->saveAttempts($email);
            (new OrderCommonService)->changeStatus($order, $email['status_id'], ['reason_id' => $email['reason_id']]);
            $in_blacklist = true;
        }

        $this->in_blacklist = $in_blacklist;
        return $in_blacklist;
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function sendNotification(Order $order): bool
    {
        $adv_phone = ($order->target_advert_id !== null) ? $order->targetAdvert->advert->profile->phone_number : null;
        $target = $order->targetAdvert->targetAdvertGroup->advert_offer_target_id ?? null;
        $phone_num = str_replace(' ', '', $this->attributes['phone']);

        if ($target !== null) {
            $query = TargetAdvertView::find()
                ->select(['send_sms_customer', 'send_sms_owner', 'sms_text_customer', 'sms_text_owner'])
                ->where(['advert_offer_target_id' => $target]);

            $template_customer = $query->andWhere(['send_sms_customer' => 1])->one();
            $template_owner = $query->andWhere(['send_sms_owner' => 1])->one();

            if ($template_customer !== null && $this->canSendSmsNow() === true) {
                $turbo_sms_id = Yii::$app->turbosms->send($template_customer->sms_text_customer, (string)$phone_num);
                $this->saveOrderSmsId($order, $turbo_sms_id);
            }

            if ($template_owner !== null && $adv_phone !== null) {
                Yii::$app->turbosms->send($template_owner->sms_text_owner, (string)$adv_phone);
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function canSendSmsNow(): bool
    {
        $now = $this->convertStrTimeToTimestampWithTimeZone('now', self::TIME_ZONE_KIEV);
        $start = $this->convertStrTimeToTimestampWithTimeZone(self::NOT_AVAILABLE_TIME_FOR_SEND_SMS_START, self::TIME_ZONE_KIEV);
        $end = $this->convertStrTimeToTimestampWithTimeZone(self::NOT_AVAILABLE_TIME_FOR_SEND_SMS_END, self::TIME_ZONE_KIEV);
        $result = true;

        if ($start <= $now && $now <= $end) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param string $time
     * @param string $timeZone
     *
     * @return int
     */
    protected function convertStrTimeToTimestampWithTimeZone(string $time, string $timeZone): int
    {
        $date = new DateTime($time, new DateTimeZone($timeZone));
        $timestamp = strtotime($date->format('Y-m-d H:i:s'));

        return $timestamp;
    }

    /**
     * @param Order $order
     * @param $order_sms_id
     * @return bool
     */
    private function saveOrderSmsId(Order $order, $order_sms_id): bool
    {
        $turbo_sms_ids = array_keys($order_sms_id);

        $turbo_sms_order = new TurboSmsOrder();
        foreach ($turbo_sms_ids as $sms_id) {
            $turbo_sms_order->order_id = $order->order_id;
            $turbo_sms_order->turbo_sms_id = $sms_id;
            $turbo_sms_order->save();
        }

        return true;
    }
}
