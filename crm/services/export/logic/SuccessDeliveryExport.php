<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 5/29/18
 * Time: 5:54 PM
 */

namespace crm\services\export\logic;

use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\services\timezone\TimeZoneSrv;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class SuccessDeliveryExport implements ExportInterface
{
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'Success Delivery Export';
        
        $orders = $this->findOrders($filters);
        $dataSheet['titles'] = $this->getTitles($orders[0]);
        $dataSheet['data'] = $orders;
        
        return $dataSheet;
    }
    
    public function countOrderByFilters($filters = null)
    {
        return $this->findOrders($filters, $count = true);
    }
    
    private function findOrders($filters = null, $count = false)
    {
        $sent_by = '';
        if ( !empty($filters['advert_id']['value'])) {
            $sent_by .= '(SELECT advert_id FROM target_advert WHERE target_advert.target_advert_id = '.$filters['advert_id']['value'].') as sent_by,';
        } else {
            $sent_by .= 'order_view.sent_by,';
        }

        $orders = OrderView::find()
            ->select($this->selectFields(), $sent_by)
            ->join('JOIN', 'customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->join('LEFT JOIN', 'order_sku', 'order_sku.order_id = order_view.order_id')
            ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id')
            ->join('LEFT JOIN', 'order_status', 'order_status.status_id = order_view.order_status')
            ->join('LEFT JOIN', 'order_status_reason', 'order_status_reason.reason_id = order_view.status_reason')
            ->andWhere(['order_view.deleted' => 0])
            ->andWhere(['IS NOT', 'order_view.delivery_date', null])
            ->andWhere(['order_view.owner_id' => $filters['advert_id']])
            ->andWhere(['order_view.order_status' => OrderStatus::SUCCESS_DELIVERY]);
        $orders = $this->filterQuery($orders, $filters);

        if ($count == true) {
            return $orders->groupBy('order_view.order_id')->count();
        }

        $result = $orders->groupBy(['order_view.order_id', 'order_sku.sku_id'])->asArray()->all(\Yii::$app->db);

        $target_advert_ids = array_keys(ArrayHelper::index($result, 'target_advert_id_s'));
        $target_advert_sku_rules=[];

        foreach ($target_advert_ids as $target_advert_id)
        {
            $target_advert_skus = TargetAdvertSku::find()->where(['target_advert_id' => $target_advert_id])->asArray()->all();
            foreach ($target_advert_skus as $key => $target_advert_sku)
            {
                if ($target_advert_sku['use_sku_cost_rules'] == true)
                {
                    $cost_rules = TargetAdvertSkuRules::find()
                        ->where(['target_advert_sku_id' => $target_advert_sku['target_advert_sku_id']])
                        ->asArray()
                        ->all();
                    $target_advert_skus[$key]['sku_cost_rules'] = ArrayHelper::index($cost_rules, 'amount');
                }
            }
            $target_advert_sku_rules[$target_advert_id]=$target_advert_skus;
        }

        $prev_order_hash = 0;
        $prev_order_sku_item_count = 0;

        foreach ($result as $key => $order_sku)
        {
            $rules = [];
            foreach ($target_advert_sku_rules[$order_sku['target_advert_id_s']] as $sku_rule) {
                if ($sku_rule['sku_id'] == $order_sku['sku_id_s']) $rules = $sku_rule;
            }


            if (!empty($rules))
            {
                for ($i = 1; $i <= $order_sku['quantity']; $i++)
                {

                    if ($order_sku['order_hash'] != $prev_order_hash || $rules['is_upsale'] === true) $sku_item_number = $i;
                    else $sku_item_number = $prev_order_sku_item_count + $i;

                    $new_order_sku_row = $order_sku;

                    if (isset($rules['sku_cost_rules']))
                    {
                        if (isset($rules['sku_cost_rules'][$sku_item_number]))
                        {
                            if (!isset($rules['sku_cost_rules'][$sku_item_number-1])) $new_order_sku_row['price_for_1_pc'] = $rules['sku_cost_rules'][$sku_item_number]['cost'];
                            else{
                                $new_order_sku_row['price_for_1_pc'] = $rules['sku_cost_rules'][$sku_item_number]['cost']-$rules['sku_cost_rules'][$sku_item_number-1]['cost'];
                            }
                        }else{
                            $new_order_sku_row['price_for_1_pc'] = $rules['exceeded_cost'];
                        }
                    }else{
                        $new_order_sku_row['price_for_1_pc'] = $rules['base_cost'];
                    }

                    $new_order_sku_row['quantity'] = 1;
                    $result[] = $new_order_sku_row;
                }

                if ($order_sku['order_hash'] != $prev_order_hash){

                    if ($rules['is_upsale'] == true) $prev_order_sku_item_count = 0;
                    else $prev_order_sku_item_count = $order_sku['quantity'];

                    $prev_order_hash = $order_sku['order_hash'];
                }
                else{
                    if ($rules['is_upsale'] != true) $prev_order_sku_item_count += $order_sku['quantity'];
                }

                unset($result[$key]);
            }

        }

//        $prev_count = 0;
//
//        foreach ($result as $key => $order_sku_row) {
//            if (isset($target_advert_sku_rules[$order_sku_row['target_advert_id_s']]) && $order_sku_row['pcs'] > 1) {
////                var_dump($target_advert_sku_rules[$order_sku_row['target_advert_id_s']]);exit;
//                $rules = [];
//                foreach ($target_advert_sku_rules[$order_sku_row['target_advert_id_s']] as $sku_rule) {
//                    if ($sku_rule['sku_id'] == $order_sku_row['sku_id_s']) $rules = $sku_rule;
//                }
//
////                var_dump($rules);exit;
//
////                if (isset($target_advert_sku_rules[$order_sku_row['target_advert_id_s']]['sku_cost_rules']))
//                if (isset($rules['sku_cost_rules'])) {
//
//                    $price = 0;
//                    if ($order_sku_row['order_hash'] == $prev_order_hash) $prev_count++;
//                    else $prev_count = 0;
//
//
//                    for ($i = 1; $i <= $order_sku_row['quantity']; $i++) {
//
//                        if (isset($rules['sku_cost_rules'][$prev_count]) && $prev_count == 1) {
//                            $price += $rules['sku_cost_rules'][$prev_count]['cost'];
//                        }elseif (isset($rules['sku_cost_rules'][$prev_count]) && $prev_count > 1) {
//                            $prev_count ++;
//                            $price += $rules['sku_cost_rules'][$prev_count]['cost']-$rules['sku_cost_rules'][$prev_count-1]['cost'];
//
//                            $order_sku_row_next = $order_sku_row;
//                            $order_sku_row_next['price_for_1_pc'] = $rules['sku_cost_rules'][$prev_count]['cost']-$rules['sku_cost_rules'][$prev_count-1]['cost'];
//                            $order_sku_row_next['quantity'] = 1;
//                            $result[] = $order_sku_row_next;
//                        }
//                        else $price += $rules['exceeded_cost'];
//                    }
//
//                    $result[$key]['price_for_1_pc'] = $price;
//                }
//
//                if (isset($rules['is_upsale']) && $rules['is_upsale'] == true) $result[$key]['price_for_1_pc'] = $rules['base_cost'];
//            }
//            $prev_order_hash = $order_sku_row['order_hash'];
//        }

        return array_values($result);
    }

    private function filterQuery($orders, $filters):ActiveQuery
    {
        if ( !empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
            $orders->andWhere(['order_view.owner_id' => $owner_id]);
        }
        if ( !empty($filters['country_id']['value'])) {
            $orders->andWhere(['order_view.country_id' => $filters['country_id']['value']]);
        }
        if ( !empty($filters['created_at']['start'] && !empty($filters['created_at']['end']))) {
            $start = new \DateTime($filters['created_at']['start']);
            $end = new \DateTime($filters['created_at']['end']);
        } else {
            $start = new \DateTime();
            $start->setDate(date('Y'), date('m'), 1);
            $end = clone $start;
            $end->setDate(date('Y'), date('m'), date('t'));
        }
        
        $start->setTime(0, 0, 0);
        $start_date = $start->format('Y-m-d H:i:s');
        
        $end->setTime(23, 59, 59);
        $end_date = $end->format('Y-m-d H:i:s');
        
        $tz = new TimeZoneSrv();
        $orders->andWhere(['between', 'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '")', $start_date, $end_date]);
        
        return $orders;
    }
    
    private function selectFields(): array
    {
        $tz = new TimeZoneSrv();
        $fields = [
            'order_view.order_hash',
            'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '") as created_at',
            'CONVERT_TZ(order_view.delivery_date, "+00:00", "' . $tz->time_zone_offset . '") as delivery_date',
            'customer_view.name',
            'customer_view.phone',
            'customer_view.address',
            'order_view.declaration',
            'order_view.offer_id as offer',
            'IFNULL(order_view.total_amount, "-") as pcs',
            'customer_view.country_id as country',
            'customer_view.city_id as emirate',
            'order_view.order_status as status',
            'order_status_reason.reason_name as reason',
            'group_concat(product_sku.sku_name ORDER BY order_sku.sku_id SEPARATOR "\r\n") as sku_count',
            'group_concat(concat(order_sku.amount) ORDER BY order_sku.sku_id SEPARATOR "\r\n") as quantity',
            'order_view.currency_name as currency',
            'group_concat(concat(order_sku.cost) ORDER BY order_sku.sku_id SEPARATOR "\r\n") as price_for_1_pc',
            'group_concat(concat((order_sku.cost) * (order_sku.amount)) ORDER BY order_sku.sku_id SEPARATOR "\r\n") as value',
            'CONVERT_TZ(order_view.delivery_date, "+00:00", "' . $tz->time_zone_offset . '") as status_change_date',
            '("-") as cod_nr',
            '("-") as Color',
            '("-") as Size',
            '("-") as Caller',
            '("-") as Time',
            'order_view.total_cost as total_cost',
            'order_view.usd_total_cost as usd_delivery_cost',
            'order_view.usd_advert_commission',
            'order_view.usd_wm_commission',
            'order_view.bitrix_flag as 1c',
            'order_view.delivery_api_id',
            '("-") as warehouse',
            'order_view.tracking_no',
            'order_view.shipment_no',
            'order_view.remote_status',
            'order_view.report_no',
            'order_view.delivery_date_in_fact',
            'order_view.money_in_fact',
            'order_view.information',
            'order_view.owner_name',
            'order_view.target_advert_id as target_advert_id_s',
            'order_sku.sku_id as sku_id_s'
        ];
        
        return $fields;
    }
    
    /**
     * @param $order
     *
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
     * 'Country',
     * 'Emirate',
     * 'Status',
     * 'Reason',
     * 'Sku',
     * 'Quantity',
     * 'Currency',
     * 'Price for 1 pc',
     * 'Value',
     * 'Status Change Date',
     * 'COD NR',
     * 'Color',
     * 'Size',
     * 'Caller',
     * 'Time',
     * 'Total cost',
     * 'Usd delivery cost',
     * 'Usd advert commission',
     * 'Usd wm commission',
     * '1c',
     * 'Delivery api name',
     * 'Warehouse',
     * 'Tracking no',
     * 'Shipment no',
     * 'Remote status',
     * 'Report no',
     * 'Delivery date in fact',
     * 'Money in fact',
     * 'Information',
     * 'Owner name',
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