<?php

namespace common\services\callcenter\call_list;

use common\models\geo\GeoArea;
use common\models\geo\GeoRegion;
use Yii;
use common\models\customer\Customer;
use common\models\customer\OldHistory;
use common\models\Instrument;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\log\LogSrv;
use yii\base\Exception;
use common\models\geo\GeoCity;

class CustomerService
{
    public $customer;

    public function __construct($customer_id)
    {
        $this->customer = Customer::findOne(['customer_id' => $customer_id]);
        $this->customer->instrument = OrderInfoInstrument::CALL_CENTER;
    }

    public function update($attributes)
    {
        unset($attributes['customer_id']);
        $this->customer->address = $attributes['address'];
        $this->customer->email = isset($attributes['email']) ? $attributes['email'] : null;
        $this->customer->phone = $attributes['phone'];
        $this->customer->name = isset($attributes['customer_name']) ? $attributes['customer_name'] : null;
        $this->customer->pin = isset($attributes['pin']) ? $attributes['pin'] : null;

        if (isset($attributes['info'])) {
            $order = $this->customer->getLastOrder();
            $order->information = $attributes['info'];
            $order->instrument = Instrument::CALL_CENTER_COMMENT;
            $order->update();
        }

        if ($this->customer->save())
            return true;
//            return $this->customer;

        return $this->customer->errors;
    }

    public function availableCities()
    {
        $cities = GeoCity::find()->select(['city_id', 'city_name'])
            ->where(['geo_id' => $this->customer->country_id])
            ->orderBy(['city_name' => SORT_ASC])
            ->asArray()->all();
        return $cities;
    }
    
    public function availableAreas($region_id): array
    {
        $areas = GeoArea::find()
            ->select([
                'area_id',
                'main_area',
                'sub_area',
                'area_known_locally',
            ])
            ->where(['region_id' => $region_id]);

        $result = $areas
            ->orderBy(['sub_area' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }

    public function saveAddress($city_id, $area_id, $address)
    {
        if ($this->customer->city_id != $city_id) {
            $this->customer->city_id = $area->region_id ?? $city_id;
        }

        $area = GeoArea::findOne(['area_id' => $area_id]);
    
        if ($area !== null) {
            $this->customer->area_id = $area->area_id;
            $this->customer->address = $area->sub_area . ' / ' . $area->area_known_locally . ', ' . $address;
        } else {
            $city = GeoCity::findOne(['city_id' => $city_id]);
            $city_name = $city->city_name ?? '';
            $this->customer->address = "$city_name, $address";
        }
        
        $this->customer->instrument = Instrument::CALL_CENTER_SET_EMIRATE;
        $this->customer->save();
    }

    public function getHistory($excepted_order_id)
    {
        $owner_id = Yii::$app->user->identity->getOwnerId();
        $phone = strlen($this->customer->phone) > 7 ? $this->customer->phone : null;

        $clear = null;
        if (strstr($phone, '9710')){
            $clear = str_replace('9710', '971', $phone);
        }

        $isAllowed = null;
        $isAllowedOld = null;

        if (!is_null($owner_id)) {
            if (is_array($owner_id)) {
                $isAllowed .= "
                if(order_data.owner_id NOT IN (" . implode(',', $owner_id) . "), '-----', `order`.`order_hash`) as `order_hash`,
                if(order_data.owner_id NOT IN (" . implode(',', $owner_id) . "), 'NDA', `offer`.`offer_name`) as `offer_name`,
                if(order_data.owner_id NOT IN (" . implode(',', $owner_id) . "), 'NDA', `user`.username) as `advert_name`";

                $isAllowedOld .= "
                if(advert_id NOT IN (" . implode(',', $owner_id) . "), 'NDA', `offer_name`) as `offer_name`,
                if(advert_id NOT IN (" . implode(',', $owner_id) . "), 'NDA', `advert_name`) as `advert_name`";
            } else {
                $isAllowed .= "
                if(order_data.owner_id != $owner_id, '-----', `order`.`order_hash`) as `order_hash`,
                if(order_data.owner_id != $owner_id, 'NDA', `offer`.`offer_name`) as `offer_name`,
                if(order_data.owner_id != $owner_id, 'NDA', `user`.username) as `advert_name`";

                $isAllowedOld .= "
                if(advert_id != $owner_id, 'NDA', `offer_name`) as `offer_name`,
                if(advert_id != $owner_id, 'NDA', `advert_name`) as `advert_name`";
            }
        } else {
            $isAllowed .= "`order`.order_hash, `offer`.offer_name, `user`.username as advert_name";
            $isAllowedOld .= "`offer_name`, `advert_name`";
        }

        $query_new = Order::find()
            ->select([
                'order.order_id',
                'customer.name',
                'customer.phone',
                'order.created_at',
                'order.customer_id',
                'order_status as order_status_id',
                'countries.country_name',
                'countries.country_code as iso',
                $isAllowed,
            ])
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = `order`.order_id')
            ->join('LEFT JOIN', 'customer', '`order`.customer_id = customer.customer_id')
            ->join('LEFT JOIN', 'countries', 'customer.country_id = countries.id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = `order`.offer_id')
            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->join('LEFT JOIN', '`user`', '`user`.id = target_advert.advert_id');

        if (!empty($phone)) {
            $query_new->where(['!=', '`order`.order_id', $excepted_order_id]);
            $query_new->andWhere(['like', 'customer.phone', $phone]);
        } else {
            $query_new->andWhere(['=', '`order`.order_id', $excepted_order_id]);
        }

        if (!empty($clear)) $query_new->orWhere(['like', 'customer.phone', $clear]);

        $history_by_customer_phone = $query_new
            ->groupBy('`order`.order_id')
            ->asArray()
            ->all();

        if (!empty($history_by_customer_phone)) {
            foreach ($history_by_customer_phone as $key => $row) {
                $history_by_customer_phone[$key]['order_status'] = OrderStatus::attributeLabels()[$row['order_status_id']];
            }
        }

        $query_old = OldHistory::find()
            ->select([
                'customer_id',
                'name',
                'phone',
                'country_name',
                'iso',
                'status',
                'created_at',
                $isAllowedOld
            ])
            ->where(['like', 'phone', $phone]);

        if (!empty($clear)) $query_old->orWhere(['like', 'phone', $clear]);

        $query_old_result = $query_old
            ->asArray()
            ->all();

        if (!empty($query_old_result)){
            foreach ($query_old_result as $k => $value) {
                $history_by_customer_phone[] = [
//                    'order_id' => '-----',
                    'order_hash' => '-----',
                    'created_at' => $value['created_at'],
                    'customer_id' => $value['customer_id'],
                    'order_status_id' => $value['status'],
                    'name' => $value['name'],
                    'phone' => $value['phone'],
                    'country_name' => $value['country_name'],
                    'iso' => $value['iso'],
                    'offer_name' => $value['offer_name'],
                    'advert_name' => $value['advert_name'],
                    'order_status' => OldHistory::oldCrmStatusLabels()[$value['status']],
                ];
            }
        }
        
        $current_order = Order::find()
            ->select(['order.order_hash', 'offer.offer_name'])
            ->leftJoin('offer', 'offer.offer_id = order.offer_id')
            ->where(['order_id' => $excepted_order_id])
            ->asArray()
            ->one();
        
        return [
            'order_hash' => $current_order['order_hash'],
            'offer_name' => $current_order['offer_name'],
            'history_by_customer_phone' => $history_by_customer_phone
        ];
    }

//    public function getHistory($excepted_order_id)
//    {
//        $owner_id = \Yii::$app->user->identity->getOwnerId();
//        $phone = strlen($this->customer->phone) > 7 ? $this->customer->phone : null;
//
////        $history_by_customer_id_query = Order::find()
////            ->select([
////                'order_id',
////                'order.created_at',
////                'order.customer_id',
////                'order.order_hash',
////                'order_status as order_status_id',
////                'customer.name',
////                'customer.phone',
////                'countries.country_name',
////                'countries.country_code as iso',
////                'offer.offer_name'
////            ])
////            ->join('LEFT JOIN', 'customer', 'order.customer_id = customer.customer_id')
////            ->join('LEFT JOIN', 'countries', 'customer.country_id = countries.id')
////            ->join('LEFT JOIN', 'offer', 'offer.offer_id = order.offer_id')
////            ->where(['order.customer_id' => $this->customer->customer_id])
////            ->andWhere(['!=', 'order.order_id', $excepted_order_id])
////            ->groupBy('order_id')
////            ->asArray()
////            ->all();
////
////        if (!empty($history_by_customer_id_query))
////        {
////            foreach ($history_by_customer_id_query as $key=>$row) $history_by_customer_id_query[$key]['order_status'] = OrderStatus::attributeLabels()[$row['order_status_id']];
//////            return $history_by_customer_id_query;
////        }
//
////        var_dump($this->customer->phone);exit;
//        $query = Order::find()
//            ->select([
//                'order.order_id',
//                'order.order_hash',
//                'order.created_at',
//                'order.customer_id',
//                'order_status as order_status_id',
//                'customer.name',
//                'customer.phone',
//                'countries.country_name',
//                'countries.country_code as iso',
//                'offer.offer_name',
//                'user.username as advert_name',
//            ])
//            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = order.order_id')
//            ->join('LEFT JOIN', 'customer', 'order.customer_id = customer.customer_id')
//            ->join('LEFT JOIN', 'countries', 'customer.country_id = countries.id')
//            ->join('LEFT JOIN', 'offer', 'offer.offer_id = order.offer_id')
//            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
//            ->join('LEFT JOIN', 'user', 'user.id = target_advert.advert_id');
//
//        if (!empty($phone)) {
//            $query->where(['like', 'customer.phone', $phone]);
//            $query->andWhere(['!=', 'order.order_id', $excepted_order_id]);
//        } else {
//            $query->andWhere(['=', 'order.order_id', $excepted_order_id]);
//        }
//        if (!is_null($owner_id)) $query->andWhere(['order_data.owner_id' => $owner_id]);
//
////        $query->andWhere(['!=', 'order.order_id', $excepted_order_id]);
//
//        $history_by_customer_phone = $query->groupBy('order.order_id')
//            ->asArray()
//            ->all();
//
////        var_dump($history_by_customer_phone);exit;
//
//        if (!empty($history_by_customer_phone)) {
//            foreach ($history_by_customer_phone as $key => $row) $history_by_customer_phone[$key]['order_status'] = OrderStatus::attributeLabels()[$row['order_status_id']];
////            return $history_by_customer_phone;
//        }
//
////        return $history_by_customer_phone + $history_by_customer_id_query;
//        return $history_by_customer_phone;
//    }
}