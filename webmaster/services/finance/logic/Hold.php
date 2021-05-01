<?php

namespace webmaster\services\finance\logic;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use webmaster\services\finance\FinanceSearchInterface;
use webmaster\services\finance\FinanceService;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class Hold
 * @package webmaster\services\finance\logic
 */
class Hold implements FinanceSearchInterface
{
    const approved = "(" .
        OrderStatus::CANCELED . ", " . OrderStatus::NOT_PAID . ", " .
        OrderStatus::WAITING_DELIVERY . ", " . OrderStatus::DELIVERY_IN_PROGRESS . ", " .
        OrderStatus::SUCCESS_DELIVERY . ", " . OrderStatus::RETURNED . ")";

    /**
     * @var FinanceService
     */
    public $financeService;

    /**
     * Hold constructor.
     */
    public function __construct()
    {
        $this->financeService = new FinanceService();
    }

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function searchLeads($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $hold = $this->holdQuery();

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $hold->andWhere(['order_data.wm_id' => $filters['wm_id']]);
        } else $hold->andWhere(['order_data.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['offer'])) $hold->andWhere(['offer.offer_id' => $filters['offer']]);
        if (isset($filters['flow'])) $hold->andWhere(['flow.flow_id' => $filters['flow']]);
        if (isset($filters['geo'])) $hold->andWhere(['wm_offer_target.geo_id' => $filters['geo']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $hold->andWhere(['>', 'order.created_at', $start_date]);
            $hold->andWhere(['<', 'order.created_at', $end_date]);
        }

        $hold->andHaving('hold_end < date AND hold_end > DATE_FORMAT( NOW(), \'%d.%m.%Y %H:%i:%s\' )');

        if (isset($sort_field)) {
            $hold->orderBy([$sort_field => $sort_order]);
        } else {
            $hold->orderBy([
                '`order`.created_at' => SORT_DESC
            ]);
        }

        $clone = clone $hold;
        $count_all = $clone->count();

        if (isset($pagination)) $hold->offset($pagination['first_row'])->limit($pagination['rows']);

        $result = $hold
            ->asArray()
            ->all();

        return [
            'hold' => $result,
            'count' => [
                'count_all' => $count_all,
            ],
        ];
    }

    /**
     * @return int|mixed
     */
    public function getHoldBalance()
    {
        $finances = Order::find()
            ->select([
                "SUM(`order`.`wm_commission`) as commission",
                "DATE_FORMAT(`order`.updated_at, '%d.%m.%Y %H:%i:%s') AS date",
                "DATE_FORMAT(DATE_ADD(`order`.updated_at, INTERVAL `target_wm_group`.`hold` DAY), '%d.%m.%Y %H:%i:%s') as hold_end"
            ], new Expression('STRAIGHT_JOIN'))
            ->leftJoin('order_data', 'order_data.order_id = `order`.order_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->where(['order_data.wm_id' => Yii::$app->user->identity->getId()])
//            ->andWhere('`order`.order_status = wm_offer_target.wm_offer_target_status')
//            ->andWhere("wm_offer_target.wm_offer_target_status != ". OrderStatus::WAITING_DELIVERY ."")
            ->andWhere(['`order`.deleted' => 0])
            ->andWhere('hold_end > date')->andWhere('hold_end < DATE_FORMAT( NOW(), "%d.%m.%Y %H:%i:%s")')
            ->asArray()
            ->one();

        $in_hold = $finances['commission'];

        return !empty($in_hold) ? $in_hold : 0;
    }

    /**
     * @return ActiveQuery
     */
    private function holdQuery(): ActiveQuery
    {
        $query = Order::find()
            ->select([
                "`order`.`order_hash`",
                "`order`.`wm_commission`",
                "`order`.`created_at`",
                "`offer`.`offer_id`",
                "`offer`.`offer_name`",
                "`flow`.`flow_id`",
                "`flow`.`flow_name`",
                "`target_wm_group`.`hold`",
                "DATE_FORMAT(`order`.updated_at, '%d.%m.%Y %H:%i:%s') AS date",
                "DATE_FORMAT(DATE_ADD(`order`.updated_at, INTERVAL `target_wm_group`.`hold` DAY), '%d.%m.%Y %H:%i:%s') as hold_end"
            ], new Expression('STRAIGHT_JOIN'))
            ->leftJoin('order_data', 'order_data.order_id = `order`.order_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->leftJoin('flow', '`order`.flow_id = flow.flow_id')
            ->leftJoin('offer', 'wm_offer_target.offer_id = offer.offer_id')
            ->where('`order`.order_status >= wm_offer_target.wm_offer_target_status')
            ->andWhere("wm_offer_target.wm_offer_target_status != ". OrderStatus::WAITING_DELIVERY ."")
            ->orWhere("wm_offer_target.wm_offer_target_status = ". OrderStatus::WAITING_DELIVERY ." AND `order`.order_status IN ". self::approved ."")
            ->andWhere(['`order`.deleted' => 0]);

        return $query;
    }
}