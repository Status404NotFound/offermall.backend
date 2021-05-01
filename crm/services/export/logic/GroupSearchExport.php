<?php

namespace crm\services\export\logic;

use Yii;
use common\models\order\Order;
use common\models\order\OrderView;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class GroupSearchExport
 * @package crm\services\export\logic
 */
class GroupSearchExport implements ExportInterface
{
    /**
     * @param null $filters
     * @return array
     * @throws Exception
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'Group Search Export';

        if (empty($orders = $this->findOrders($filters)))
            throw new Exception('No orders.');

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
        $orders = OrderView::find()->select($this->selectFields(), new Expression('STRAIGHT_JOIN'))
            ->join('JOIN', 'customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->join('LEFT JOIN', 'order_sku', 'order_sku.order_id = order_view.order_id')
            ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id')
            ->join('LEFT JOIN', 'countries', 'countries.id = customer_view.country_id')
            ->join('LEFT JOIN', 'order_status', 'order_status.status_id = order_view.order_status')
            ->where(['order_view.deleted' => 0]);

        $orders = $this->filterQuery($orders, $filters);
        return $orders->groupBy('order_view.order_hash')->asArray()->all();
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

        if (isset($filters['order_hash']['value'])) $orders->andWhere(['order_view.order_hash' => $filters['order_hash']['value']]);

        return $orders;
    }

    /**
     * @return array
     */
    private function selectFields(): array
    {
        $fields = [
            'order_view.order_hash',
            'order_view.declaration',
            'order_view.offer_name as offer',
            'order_view.delivery_date',
            'order_view.country_name as country',

            'customer_view.name',
            'customer_view.phone',
            'customer_view.address',
            'customer_view.email',

            'customer_view.city_name as region',

            'product_sku.sku_name as sku_name',
            'order_view.total_amount as pcs',
            'order_view.total_cost as cost',

            'order_status.status_name as status',
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