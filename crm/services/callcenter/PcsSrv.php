<?php
/**
 * Created by PhpStorm.
 * User: ihor-fish
 * Date: 19.10.17
 * Time: 12:58
 */

namespace crm\services\callcenter;


use common\models\callcenter\OperatorPcs;

class PcsSrv
{
    public $pcs;
    public $count;
    public $pcs_total;

    public function __construct($params)
    {
        $data = $this->getPieces($params);
        $this->pcs = $data['pcs'];
        $this->pcs_total = $data['pcs_total'];
    }

    protected function getPieces($params)
    {
        $filters = $params['filters'];
        $owner_id = \Yii::$app->user->identity->getOwnerId();
        $pcs_query = OperatorPcs::find()->select([
            'operator_pcs.operator_pcs_id',
            'pcs_new',
            'pcs_old',
            'sum(up_sale) as up_sale',
            'user.username',
            'order.created_at as order_created_at',
            'order.order_hash',
            'order.order_id',
            'order.order_status as order_status_id',
            'offer.offer_id',
            'offer.offer_name',
            'operator_pcs.created_at as call_date',
            'count(order.order_id) as total',
            'country_code as country_iso',
            'country_name',
            'advert_offer_target.geo_id as country_id',
            ])
            ->join('LEFT JOIN', 'user', 'operator_pcs.operator_id = user.id')
            ->join('LEFT JOIN', 'order', 'order.order_id = operator_pcs.order_id')
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = order.order_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = order.offer_id')
            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'countries', 'advert_offer_target.geo_id=countries.id')
            ->where(['order.deleted' => 0]);

        if ($owner_id !== null) $pcs_query->andWhere(['order_data.owner_id' => $owner_id]);
        if (isset($filters['operator_pcs_id'])) $pcs_query->andWhere(['operator_pcs_id' => $filters['operator_pcs_id']['value']]);
        if (isset($filters['username'])) $pcs_query->andWhere(['operator_id' => $filters['username']['value']]);
        if (isset($filters['order_id'])) $pcs_query->andWhere(['order.order_hash' => $filters['order_id']['value']]);
        if (isset($filters['offer_id'])) $pcs_query->andWhere(['offer.offer_id' => $filters['offer_id']['value']]);
        if (isset($filters['pcs_old'])) $pcs_query->andWhere(['pcs_old' => $filters['pcs_old']['value']]);
        if (isset($filters['pcs_new'])) $pcs_query->andWhere(['pcs_new' => $filters['pcs_new']['value']]);
        if (isset($filters['up_sale'])) $pcs_query->andWhere(['up_sale' => $filters['up_sale']['value']]);
        if (isset($filters['order_status'])) $pcs_query->andWhere(['order.order_status' => $filters['order_status']['value']]);
        if (isset($filters['country_id'])) $pcs_query->andWhere(['advert_offer_target.geo_id' => $filters['country_id']['value']]);
        if (isset($filters['visibility']) && $filters['visibility']['value'] == 0) {
            $pcs_query->andWhere(['>', 'operator_pcs.up_sale', 0]);
        }
        if (isset($filters['call_at']))
        {
            $start = new \DateTime($filters['call_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['call_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $pcs_query->andWhere(['>', 'operator_pcs.created_at',$start_date ]);
            $pcs_query->andWhere(['<', 'operator_pcs.created_at', $end_date]);
        }
        if (isset($filters['created_at']))
        {
            $start = new \DateTime($filters['created_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['created_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $pcs_query->andWhere(['>', 'order.created_at',$start_date ]);
            $pcs_query->andWhere(['<', 'order.created_at', $end_date]);
        }

        $pcs_query->groupBy('operator_pcs_id');
        $this->count = (clone $pcs_query)->count();
        
        return [
            'pcs' => (clone $pcs_query)->orderBy(['operator_pcs_id' => SORT_DESC])
                ->offset($params['firstRow'])
                ->limit($params['rows'])
                ->asArray()
                ->all(),
            'pcs_total' => (clone $pcs_query)->groupBy('operator_id')
                ->asArray()
                ->all(),
        ];
    }
    
    public function addPcs($order_id, $pcs_old, $pcs_new)
    {

    }
}