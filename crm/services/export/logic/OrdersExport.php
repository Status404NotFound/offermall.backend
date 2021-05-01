<?php

namespace crm\services\export\logic;

use Yii;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\modules\user\models\tables\User;
use common\modules\user\models\Permission;
use common\helpers\FishHelper;
use common\services\timezone\TimeZoneSrv;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class OrdersExport
 * @package crm\services\export\logic
 */
class OrdersExport implements ExportInterface
{
    /**
     * @param null $filters
     * @return array
     * @throws Exception
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'Orders Export';

        if (empty($orders = $this->findOrders($filters)))
            throw new Exception('No orders.');

        $dataSheet['titles'] = $this->getTitles($orders[0]);
//        $dataSheet['formats'] = [
//            'B' => 'dd-mm-yyyy h:i:s',
//            'C' => 'dd-mm-yyyy h:i:s',
//            'Q' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
//        ];
//        $dataSheet['formatters'] = [
//            'B' => function ($value, $row, $data) {
//                return \PHPExcel_Shared_Date::PHPToExcel(strtotime($value));
//            },
//            'C' => function ($value, $row, $data) {
//                return \PHPExcel_Shared_Date::PHPToExcel(strtotime($value));
//            },
//        ];
        $dataSheet['data'] = $orders;
        return $dataSheet;
    }

    /**
     * @param null $filters
     * @return array|Order[]
     */
    private function findOrders($filters = null)
    {
        $permissions = (new Permission(Yii::$app->user->identity->role, Yii::$app->user->identity->getId()))->permissions;

        $orders = OrderView::find()->select($this->selectFields())
            ->join('JOIN', 'customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->join('LEFT JOIN', 'order_sku', 'order_sku.order_id = order_view.order_id')
            ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id')
            ->join('LEFT JOIN', 'order_status', 'order_status.status_id = order_view.order_status')
            ->join('LEFT JOIN', 'order_stickers', 'order_stickers.order_id = order_view.order_id')
            ->join('LEFT JOIN', 'delivery_stickers', 'delivery_stickers.sticker_id = order_stickers.sticker_id')
            ->join('LEFT JOIN', 'order_status_reason', 'order_status_reason.reason_id = order_view.status_reason')
            ->andWhere(['order_view.deleted' => 0]);

        if (in_array(Permission::viewSkuListAdvanced, array_keys($permissions))) {
            $orders->addSelect(['order_sku.sku_id', 'order_sku.order_id',]);
        }

        $orders = $this->filterQuery($orders, $filters);
        $result = $orders->groupBy('order_view.order_hash')->asArray()->all();

        if (in_array(Permission::viewSkuListAdvanced, array_keys($permissions))) {
            foreach ($result as &$value) {
                $skus = $this->getOrderSku($value['order_id']);

                $value['sku_1'] = '(Not set)';
                $value['sku_1_count'] = '(Not set)';
                $value['sku_2'] = '(Not set)';
                $value['sku_2_count'] = '(Not set)';
                $value['sku_3'] = '(Not set)';
                $value['sku_3_count'] = '(Not set)';
                $value['sku_4'] = '(Not set)';
                $value['sku_4_count'] = '(Not set)';
                $value['sku_5'] = '(Not set)';
                $value['sku_5_count'] = '(Not set)';
                $value['sku_6'] = '(Not set)';
                $value['sku_6_count'] = '(Not set)';
                $value['sku_7'] = '(Not set)';
                $value['sku_7_count'] = '(Not set)';
                $value['sku_8'] = '(Not set)';
                $value['sku_8_count'] = '(Not set)';
                $value['sku_9'] = '(Not set)';
                $value['sku_9_count'] = '(Not set)';
                $value['sku_10'] = '(Not set)';
                $value['sku_10_count'] = '(Not set)';

                foreach ($skus as $sku) {
                    if ($sku['order_id'] == $value['order_id']) {

                        $value['sku_1'] = (isset($skus[0])) ? $skus[0]['sku_name'] : '(Not set)';
                        $value['sku_1_count'] = (isset($skus[0]['amount'])) ? $skus[0]['amount'] : '(Not set)';

                        $value['sku_2'] = (isset($skus[1])) ? $skus[1]['sku_name'] : '(Not set)';
                        $value['sku_2_count'] = (isset($skus[1]['amount'])) ? $skus[1]['amount'] : '(Not set)';

                        $value['sku_3'] = (isset($skus[2])) ? $skus[2]['sku_name'] : '(Not set)';
                        $value['sku_3_count'] = (isset($skus[2]['amount'])) ? $skus[2]['amount'] : '(Not set)';

                        $value['sku_4'] = (isset($skus[3])) ? $skus[3]['sku_name'] : '(Not set)';
                        $value['sku_4_count'] = (isset($skus[3]['amount'])) ? $skus[3]['amount'] : '(Not set)';

                        $value['sku_5'] = (isset($skus[4])) ? $skus[4]['sku_name'] : '(Not set)';
                        $value['sku_5_count'] = (isset($skus[4]['amount'])) ? $skus[4]['amount'] : '(Not set)';

                        $value['sku_6'] = (isset($skus[5])) ? $skus[5]['sku_name'] : '(Not set)';
                        $value['sku_6_count'] = (isset($skus[5]['amount'])) ? $skus[5]['amount'] : '(Not set)';

                        $value['sku_7'] = (isset($skus[6])) ? $skus[6]['sku_name'] : '(Not set)';
                        $value['sku_7_count'] = (isset($skus[6]['amount'])) ? $skus[6]['amount'] : '(Not set)';

                        $value['sku_8'] = (isset($skus[7])) ? $skus[7]['sku_name'] : '(Not set)';
                        $value['sku_8_count'] = (isset($skus[7]['amount'])) ? $skus[7]['amount'] : '(Not set)';

                        $value['sku_9'] = (isset($skus[8])) ? $skus[8]['sku_name'] : '(Not set)';
                        $value['sku_9_count'] = (isset($skus[8]['amount'])) ? $skus[8]['amount'] : '(Not set)';

                        $value['sku_10'] = (isset($skus[9])) ? $skus[9]['sku_name'] : '(Not set)';
                        $value['sku_10_count'] = (isset($skus[9]['amount'])) ? $skus[9]['amount'] : '(Not set)';
                    }
                }
                unset($value['order_id']);
                unset($value['sku_id']);
            }
        }

        return $result;
    }

    private function getOrderSku($order_id)
    {
        $order_sku = OrderSku::find()
            ->select(['product_sku.sku_name', 'order_sku.amount', 'order_sku.order_id'])
            ->leftJoin('product_sku', 'product_sku.sku_id = order_sku.sku_id')
            ->where(['order_id' => $order_id])
            ->asArray()->all();

        return $order_sku;
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

        if (isset($filters['owner_id'])) $orders->andWhere(['order_view.owner_id' => $filters['owner_id']['value']]);
        if (isset($filters['advert_target_id'])) $orders->andWhere(['order_view.advert_offer_target_status' => $filters['advert_target_id']['value']]);
        if (isset($filters['country_id'])) $orders->andWhere(['order_view.country_id' => $filters['country_id']['value']]);
        if (isset($filters['offer_id'])) $orders->andWhere(['order_view.offer_id' => $filters['offer_id']['value']]);
        if (isset($filters['status']['value'])) $orders->andWhere(['order_view.order_status' => $filters['status']['value']]);
        if (isset($filters['status_reason']['value'])) $orders->andWhere(['order_view.status_reason' => $filters['status_reason']['value']]);
        if (isset($filters['webmaster'])) $orders->andWhere(['order_view.wm_id' => $filters['webmaster']['value']]);

        if (isset($filters['created_at'])) {
            $start = new \DateTime($filters['created_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['created_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $orders->andWhere(['>', 'order_view.created_at', $start_date]);
            $orders->andWhere(['<', 'order_view.created_at', $end_date]);

//            $tz = new TimeZoneSrv();
//            $orders->andWhere(['>', 'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '")', $start_date]);
//            $orders->andWhere(['<', 'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '")', $end_date]);
        }

        return $orders;
    }

    /**
     * @return array
     */
    private function selectFields(): array
    {
//        $tz = new TimeZoneSrv();
        $fields = [
            'order_view.order_hash',

//            'CONVERT_TZ(order_view.created_at, "+00:00", "' . $tz->time_zone_offset . '") as created_at',
//            'CONVERT_TZ(order_view.delivery_date, "+00:00", "' . $tz->time_zone_offset . '") as delivery_date',

            'order_view.created_at',
            'order_view.delivery_date',

            'customer_view.name',
            'customer_view.phone',
            'customer_view.address',
            'order_view.declaration',
            'order_view.offer_name as offer',
            'IFNULL(order_view.total_amount, "-") as pcs',
            'order_view.country_name as country',
            'IFNULL( concat(customer_view.country_name, ", ", customer_view.city_name), "-" ) as emirate',
            'order_status.status_name as status',
            'order_status_reason.reason_name as reason',

            'GROUP_CONCAT(CONCAT(product_sku.sku_name, \': \', order_sku.amount) ORDER BY order_sku.sku_id SEPARATOR \'; \') as Sku_count',

            '("-") as Color', '("-") as Size', '("-") as Caller', '("-") as Time',

            'order_view.total_cost',
            'order_view.usd_total_cost as usd_delivery_cost',
            'order_view.usd_advert_commission',
            'order_view.usd_wm_commission',
            'order_view.bitrix_flag as 1c',
            'order_view.delivery_api_name',
            'order_view.tracking_no',
            'order_view.shipment_no',
            'order_view.remote_status',
            'order_view.report_no',
            'order_view.delivery_date_in_fact',
            'order_view.money_in_fact',
            'delivery_stickers.sticker_name',
            'order_view.information',
            'order_view.owner_name',
            'order_view.wm_name',
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