<?php

namespace crm\services\export\logic;

use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\db\ActiveQuery;

class DipDropshipAeExport implements ExportInterface
{
    /**
     * @param null $filters
     * @return array
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'DIP Dropship Export';
        $orders = $this->findOrders($filters);
        foreach ($orders as &$order) {
            $order_skus = OrderSku::findAll(['order_id' => $order['order_id']]);
            unset($order['order_id']);
            $order['product_description'] = $order['offer_name'] . ': ';
            foreach ($order_skus as $order_sku) {
                $order['product_description'] .= isset($order_sku->amount) ? $order_sku->sku->sku_name . ' * ' . $order_sku->amount . '; ' : '';
            }
            unset($order['offer_name']);
            $order['note'] = 'na';
        }

        $titles = ['none'];
        if (isset($orders[0])) $titles = $this->getTitles($orders[0]);
        $dataSheet['titles'] = $titles;
        $dataSheet['formats'] = [
            'A' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
            'D' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
        ];
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
            ->where(['order_view.order_status' => OrderStatus::DELIVERY_IN_PROGRESS]);

        if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId()))
            $orders->andWhere(['order_view.owner_id' => $owner_id]);

        if (!empty($filters['order_id_array'])) {
            $orders->andWhere(['IN', 'order_view.order_id', $filters['order_id_array']]);
        } else {
            $orders = $this->filterQuery($orders, $filters);
        }
        return $orders->asArray()->all();
    }


    /**
     * @param ActiveQuery $orders
     * @param array $filters
     * @return ActiveQuery $orders
     */
    private function filterQuery($orders, $filters)
    {
//        if (isset($filters['country_id']['value'])) $orders->andWhere(['customer_view.country_id' => $filters['country_id']['value']]);
        return $orders;
    }

    /**
     * @return array
     */
    private function selectFields(): array
    {
        $fields = [
            'order_view.order_id',
            'order_view.order_hash as client_order_ref',
            'customer_view.name as customer_name',
            'customer_view.email as customer_email',
            'customer_view.phone as customer_phone',
            'customer_view.address as customer_street',
            'customer_view.country_name as customer_country',
            'customer_view.city_name as customer_city',
            '("COD") as payment_type',
            'order_view.total_cost as amount',
            '("1") as number_of_pieces',

//            'GROUP_CONCAT(CONCAT(product_sku.sku_name, " * ", order_sku.amount) ORDER BY order_sku.sku_id SEPARATOR "; ") as product_description',

            'order_view.offer_name',
//            '("na") as note',
        ];
        return $fields;
    }

    /**
     * @param $order
     * @return array [
     * 'Client Order Ref',
     * 'Customer Name',
     * 'Customer Email',
     * 'Customer Phone',
     * 'Customer Street',
     * 'Customer Country',
     * 'Customer City',
     * 'Payment Type',
     * 'Amount',
     * 'Nubmer Of Pieces',
     * 'Product Description',
     * 'Note',
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