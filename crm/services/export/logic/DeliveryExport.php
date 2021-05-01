<?php

namespace crm\services\export\logic;

use common\helpers\FishHelper;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\services\timezone\TimeZoneSrv;
use Yii;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\db\ActiveQuery;

class DeliveryExport implements ExportInterface
{
    /**
     * @param null $filters
     * @return array
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'Delivery Export';

        $orders = $this->findOrders($filters);
        $dataSheet['titles'] = $this->getTitles($orders[0]);

        $dataSheet['data'] = $orders;
        return $dataSheet;
    }

    /**
     * @param null $filters
     * @return array|Order[]
     */
    private function findOrders($filters = null)
    {
        $orders = OrderView::find()->select($this->selectFields())
            ->join('JOIN', 'customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->join('LEFT JOIN', 'order_sku', 'order_sku.order_id = order_view.order_id')
            ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id')
            ->join('LEFT JOIN', 'order_status', 'order_status.status_id = order_view.order_status')
            ->andWhere(['order_view.deleted' => 0])
            ->andWhere(['IS NOT', 'order_view.delivery_date', null]);
        if (!empty($filters['order_id_array'])) {
            $orders->andWhere(['IN', 'order_view.order_id', $filters['order_id_array']]);
        } else {
            $orders = $this->filterQuery($orders, $filters);
        }
        return $orders->groupBy('order_view.order_id')->asArray()->all(Yii::$app->db);
    }


    /**
     * @param ActiveQuery $orders
     * @param array $filters
     * @return ActiveQuery $orders
     */
    private function filterQuery($orders, $filters)
    {
        if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId()))
            $orders->andWhere(['order_view.owner_id' => $owner_id]);

        if (isset($filters['order_hash'])) $orders->andWhere(['LIKE', 'order_view.order_hash', $filters['order_hash']['value']]);
        if (isset($filters['phone'])) $orders->andWhere(['LIKE', 'customer_view.phone', $filters['phone']['value']]);
        if (isset($filters['owner_id'])) $orders->andWhere(['order_view.owner_id' => $filters['owner_id']['value']]);
        if (isset($filters['country_id'])) $orders->andWhere(['order_view.country_id' => $filters['country_id']['value']]);

        if (isset($filters['created_at'])) {
            $start = new \DateTime($filters['created_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['created_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $tz = new TimeZoneSrv();
            $orders->andWhere(['>', 'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '")', $start_date]);
            $orders->andWhere(['<', 'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '")', $end_date]);
        }

        if ($filters['tab_name']['value'] == 'present') {
            $orders->andWhere(['>', 'DATE(order_view.delivery_date)', date('Y-m-d')]);
        } elseif ($filters['tab_name']['value'] == 'history') {
            $orders->andWhere(['<', 'DATE(order_view.delivery_date)', date('Y-m-d')]);
        }

        return $orders;
    }

    /**
     * @return array
     */
    private function selectFields(): array
    {
        $tz = new TimeZoneSrv();
        $fields = [
            'order_view.order_hash',

            'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '") as created_at',
            'CONVERT_TZ(order_view.delivery_date, "+00:00", "' . $tz->time_zone_offset . '") as delivery_date',
//            'order_view.delivery_date',

            'customer_view.name',
            'customer_view.phone',
            'customer_view.address',
            'order_view.declaration',
            'order_view.offer_name as offer',
            'IFNULL(order_view.total_amount, "-") as pcs',
            'customer_view.country_name as country',
            'IFNULL( concat(customer_view.country_name, ", ", customer_view.city_name), "-" ) as emirate',
            'order_status.status_name as status',

            'GROUP_CONCAT(CONCAT(product_sku.sku_name, \': \', order_sku.amount) ORDER BY order_sku.sku_id SEPARATOR \'; \') as Sku_count',

//            '("-") as Color', '("-") as Size', '("-") as Caller', '("-") as Time',
//
//            'order_view.usd_total_cost as usd_delivery_cost',
//            'order_view.usd_advert_commission',
//            'order_view.usd_wm_commission',
//            'order_view.bitrix_flag as 1c',
//            'order_view.delivery_api_name',
//            'order_view.tracking_no',
//            'order_view.shipment_no',
//            'order_view.remote_status',
//            'order_view.report_no',
//            'order_view.delivery_date_in_fact',
//            'order_view.money_in_fact',
//            'order_view.information',
        ];
        return $fields;
    }

    /**
     * @param $order
     * @return array [
     * 'Order hash',
     * 'Created at',
     * 'Delivery date',
     * 'Name',
     * 'Phone',
     * 'Address',
     * 'Declaration',
     * 'Offer',
     * 'Pcs',
     * 'Sku count',
     * 'Color',
     * 'Size',
     * 'Emirate',
     * 'Caller',
     * 'Time',
     * 'Status'
     * ]
     **/
    private function getTitles($order)
    {
        return explode(',',
            str_replace('_', ' ',
                ucwords(
                    implode(',',
                        array_keys($order)
                    ), ','
                )
            )
        );
    }
}