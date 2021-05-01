<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 12.06.17
 * Time: 17:38
 */

namespace common\services\callcenter;


use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\timezone\TimeZoneSrv;
use yii\base\Model;
use Yii;

class DeliveryService
{
    public $list;
    public $count;

    public function __construct(array $params = [])
    {
        $this->list = $this->searchDelivery($params);
    }

    public function searchDelivery($params){

        $owner_id  = Yii::$app->user->identity->getOwnerId();
        $filters = $params['filters'];
        $type = $params['type'];
        $tz = new TimeZoneSrv();

        $query = Order::find();
        $query->select([
            'customer.name',
            'customer.phone',
            'customer.phone_country_code',
            'customer.address',
            'customer.region_id',
            'customer.city_id',
            'customer.country_id',
            'customer.customer_id',


            'countries.country_name as emirate',
            'countries.country_code as iso',

            '`order`.order_status as order_status_id',
            '`order`.order_id',
            '`order`.order_hash',
            'order.total_amount as pcs',
            "convert_tz(`order`.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            "convert_tz(`order`.delivery_date, '+00:00', '" . $tz->time_zone_offset . "') as delivery_date",

            'offer.offer_name',
            'offer.offer_id',

            'group_concat(product_sku.sku_name) as sku',
            'group_concat(product_sku.color) as color',
        ]);

        $query->join('LEFT JOIN', 'offer', '`order`.offer_id = offer.offer_id');
        $query->join('LEFT JOIN', 'order_data', '`order`.order_id = order_data.order_id');
        $query->join('LEFT JOIN', 'customer', '`order`.customer_id = customer.customer_id');
        $query->join('LEFT JOIN', 'countries', 'countries.id = customer.country_id');
        $query->join('LEFT JOIN', 'order_sku', 'order_sku.order_id = order.order_id');
        $query->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id');

        $query->andWhere(['!=', 'order_status', OrderStatus::PENDING]);
        $query->andWhere(['!=', 'order_status', OrderStatus::BACK_TO_PENDING]);
        $query->andWhere(['!=', 'order_status', OrderStatus::NOT_VALID_CHECKED]);

        if (!is_null($owner_id)) $query->andWhere(['order_data.owner_id' =>$owner_id]);

        if (isset($filters['order_hash'])) $query->andWhere(['order.order_hash' => $filters['order_hash']['value']]);
        if (isset($filters['name'])) $query->andWhere(['like', 'customer.name', $filters['name']['value']]);
        if (isset($filters['phone'])) $query->andWhere([ 'like', 'customer.phone', str_replace(array('+','-', ' '), '', $filters['phone']['value'])]);
        if (isset($filters['address'])) $query->andWhere(['like', 'customer.address', $filters['address']['value']]);
        if (isset($filters['offer'])) $query->andWhere(['offer.offer_id' => $filters['offer']['value']]);
        if (isset($filters['order_status'])) $query->andWhere(['order_status' => $filters['order_status']['value']]);
        if (isset($filters['country'])) $query->andWhere(['customer.country_id' => $filters['country']['value']]);

        if (isset($filters['date']))
        {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`order`.delivery_date',$start_date ]);
            $query->andWhere(['<', '`order`.delivery_date', $end_date]);
        }

        if (isset($filters['created_at']))
        {
            $start = new \DateTime($filters['created_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['created_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'order.created_at',$start_date ]);
            $query->andWhere(['<', 'order.created_at', $end_date]);
        }

        $query_count = clone $query;
        $result = $query->groupBy('order.order_id')->orderBy(['order.order_id' => SORT_DESC])
            ->offset($params['firstRow'])
            ->limit($params['rows'])
            ->asArray()
            ->all();

        $this->count = $query_count->count();
        foreach ($result as $key=>$row)
        {
            $result[$key]['order_status'] = OrderStatus::attributeLabels($row['order_status_id']);
        }
        return $result;
    }
}