<?php

namespace crm\services\export\logic;

use common\helpers\FishHelper;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\base\Exception;
use yii\db\ActiveQuery;

class WfdFulfillmentSbExport implements ExportInterface
{

    /**
     * @param null $filters
     * @return array
     * @throws Exception
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'WFD Fulfillment Sb';

        if (empty($orders = $this->findOrders($filters)))
            throw new Exception('Rows not found.');

        foreach ($orders as &$order) {
            $order_skus = OrderSku::findAll(['order_id' => $order['order_id']]);
            unset($order['order_id']);

            $order['mena_sku'] = '';
            foreach ($order_skus as $order_sku) {
                $order['mena_sku'] .= isset($order_sku->amount) ? $order_sku->sku->sku_name . ' * ' . $order_sku->amount . '; ' : '';
            }
        }

        $dataSheet['titles'] = $this->getTitles($orders[0]);
        $dataSheet['formats'] = [
            'A' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
            'B' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
            'L' => \PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
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
        $orders = Order::find()->select($this->selectFields())
            ->from('order_view OV, customer_view CV')
            ->where(['OV.order_status' => OrderStatus::WAITING_DELIVERY])
            ->andWhere('CV.customer_id = OV.customer_id');

        if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId()))
            $orders->andWhere(['OV.owner_id' => $owner_id]);
        if (!empty($filters['order_id_array'])) {
            $orders->andWhere(['IN', 'OV.order_id', $filters['order_id_array']]);
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
//        if (isset($filters['country_id']['value'])) $orders->andWhere(['CV.country_id' => $filters['country_id']['value']]);
        return $orders;
    }

    /**
     * @return array
     */
    private function selectFields(): array
    {
        $fields = [
            'OV.order_id',
            'OV.order_hash as fulfillment_line_item_id',
            'OV.order_hash as order_number',
            'OV.total_amount as quantity',

            'CV.name as shipment_address_name',
            'CV.address as shipment_address_street',
            'CV.address as shipment_address_street_2',
            'CV.city_name as shipment_address_emirate',

            'OV.iso AS shipment_address_stat',
            '("0") AS shipment_address_postal_code',

            'OV.offer_name AS item_name',
            '("") AS mena_sku',

            'CV.phone as customer_phone',

            'OV.total_cost AS COD',
            '("0") AS sell_price',
            '("na") AS client_comments',
            'OV.country_name as country',
        ];
        return $fields;
    }

    /**
     * @param $order
     * @return array
     */
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