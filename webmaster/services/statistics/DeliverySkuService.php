<?php

namespace webmaster\services\statistics;

use Yii;
use yii\db\ActiveQuery;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use yii\db\Expression;

/**
 * Class DeliverySkuService
 * @package webmaster\services\statistics
 */
class DeliverySkuService
{
    /**
     * @return ActiveQuery
     */
    private function deliverySkuQuery(): ActiveQuery
    {
        return OrderSku::find()
            ->select([
                'product_sku.sku_id',
                'product_sku.sku_name',
                'offer.offer_id',
                'offer.offer_name',
                'SUM(order_sku.amount) as amount'
            ], new Expression('STRAIGHT_JOIN'))
            ->join('LEFT JOIN', 'product_sku', 'order_sku.sku_id = product_sku.sku_id')
            ->join('LEFT JOIN', 'order', '`order`.order_id = order_sku.order_id')
            ->join('LEFT JOIN', 'target_wm', '`order`.target_wm_id = target_wm.target_wm_id')
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('LEFT JOIN', 'offer', '`order`.offer_id = offer.offer_id')
            ->join('LEFT JOIN', 'geo', 'wm_offer_target.geo_id = geo.geo_id')
            ->where(['`order`.deleted' => 0])
            ->andWhere(['target_wm.wm_id' => Yii::$app->user->identity->getId()]);
    }

    /**
     * @param null $filters
     * @param null $pagination
     * @param null $sort_order
     * @return array
     */
    public function deliverySku($filters = null, $pagination = null, $sort_order = null): array
    {
        $query = $this->deliverySkuQuery();

        if (isset($filters['offer_id'])) $query->andWhere(['wm_offer_target.offer_id' => $filters['offer_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['geo.geo_id' => $filters['geo_id']]);
        if (isset($filters['status_id'])) $query->andWhere(['`order`.order_status' => $filters['status_id']]);
        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['wm_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`order`.created_at', $start_date]);
            $query->andWhere(['<', '`order`.created_at', $end_date]);
        }

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $count = clone $query;
        $count_all = $count->groupBy(['order_sku.sku_id'])->count();

        if (isset($sort_field)) $query->orderBy([$sort_field => $sort_order]);

        $result = $query
            ->groupBy(['order_sku.sku_id'])
            ->asArray()
            ->all();

        $sku_count = $count
            ->addSelect(['`order`.order_status'])
            ->groupBy(['order.order_status', 'sku_name'])
            ->asArray()
            ->all();

        foreach ($result as $key => $value) {
            foreach ($sku_count as $k => $item) {
                if ($value['sku_id'] == $item['sku_id']) {
                    $result[$key]['status_name'][] = OrderStatus::attributeLabels($item['order_status']) . ' (' . $item['amount'] . ') ';
                }
            }
        }

        return [
            'result' => $result,
            'count' => [
                'count_all' => $count_all
            ],
        ];
    }
}