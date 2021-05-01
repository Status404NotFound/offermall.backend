<?php

namespace webmaster\services\finance\logic;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\webmaster\WmCheckout;
use common\services\webmaster\Helper;
use webmaster\services\finance\FinanceSearchInterface;
use webmaster\services\finance\FinanceService;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class Finance
 * @package webmaster\services\finance\logic
 */
class Finance implements FinanceSearchInterface
{
    const approved = "(" .
        OrderStatus::CANCELED . ", " . OrderStatus::NOT_PAID . ", " .
        OrderStatus::WAITING_DELIVERY . ", " . OrderStatus::DELIVERY_IN_PROGRESS . ", " .
        OrderStatus::SUCCESS_DELIVERY . ", " . OrderStatus::RETURNED . ")";

    const not_valid_statuses = "(" .
        OrderStatus::NOT_VALID . ", " .
        OrderStatus::NOT_VALID_CHECKED . " )";

    /**
     * @var FinanceService
     */
    public $financeService;

    /**
     * Finance constructor.
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
        $query = $this->financeQuery();

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['order_data.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['order_data.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['offer_id'])) $query->andWhere(['wm_offer_target.offer_id' => $filters['offer_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['wm_offer_target.geo_id' => $filters['geo_id']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'order.created_at', $start_date]);
            $query->andWhere(['<', 'order.created_at', $end_date]);
        }

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy([
                '`order`.created_at' => SORT_DESC
            ]);
        }

        $clone = clone $query;
        $count_all = $clone
            ->groupBy(['date', 'offer_name'])
            ->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $finances = [
            'finances_list' => $query
                ->groupBy(['date', 'offer_name'])
                ->asArray()
                ->all(),
            'totals' => $clone
                ->groupBy(['date', 'offer_name'])
                ->asArray()
                ->all(),
        ];

        foreach ($finances['finances_list'] as $key => $value) {
            $finances['finances_list'][$key]['commission'] = (new Helper())->getWmRules($value['target_wm_id']);
        }

        return [
            'finance_list' => $finances['finances_list'],
            'totals' => $finances['totals'],
            'count' => [
                'count_all' => $count_all
            ],
        ];
    }

    /**
     * @return int|mixed
     */
    public function getBalance()
    {
        $finances = Order::find()
            ->select([
                "SUM(if(`order`.order_status not in " . self::not_valid_statuses . ", `order`.wm_commission, 0)) AS total",
            ], new Expression('STRAIGHT_JOIN'))
            ->leftJoin('order_data', 'order_data.order_id = `order`.order_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->where(['`order`.deleted' => 0])
            ->andWhere(['order_data.wm_id' => Yii::$app->user->identity->getId()])
            ->orWhere("wm_offer_target.wm_offer_target_status = ". OrderStatus::WAITING_DELIVERY ." AND `order`.order_status IN ". self::approved ."")
            ->andWhere(['order_data.wm_id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->one();

        $checkouts = WmCheckout::find()
            ->select([
                'SUM(amount) as amount'
            ])
            ->where(['wm_id' => Yii::$app->user->identity->getId()])
            ->andWhere(['status' => WmCheckout::PAID_OUT])
            ->asArray()
            ->one();

        $balance = $finances['total'];
        $check_sum = $checkouts['amount'];

        return $balance - $check_sum;
    }

    /**
     * @return ActiveQuery
     */
    private function financeQuery(): ActiveQuery
    {
        $query = Order::find()
            ->select([
                "`order`.`wm_commission`",
                "`offer`.`offer_name`",
                "`flow`.`flow_id`",
                "`target_wm`.`target_wm_id`",
                "`target_wm_group`.`hold`",
                "DATE_FORMAT(`order`.created_at, '%d.%m.%Y') AS date",
                "SUM(if(`order`.order_status not in " . self::not_valid_statuses . ", 1, 0)) AS leads",
                "SUM(if(`order`.order_status not in " . self::not_valid_statuses . ", `order`.wm_commission, 0)) AS total",
                "IF(`target_wm_group`.`use_commission_rules` = 1, SUM(if(`order`.order_status not in " . self::not_valid_statuses . ", `order`.total_amount, 0)), 0) as pcs"
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
            //            ->where('`order`.order_status >= wm_offer_target.wm_offer_target_status')
//            ->orWhere(['order_status' => [OrderStatus::NOT_PAID, OrderStatus::CANCELED]])
            ->andWhere(['`order`.deleted' => 0]);

        return $query;
    }
}