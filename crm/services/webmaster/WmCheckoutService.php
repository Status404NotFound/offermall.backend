<?php

namespace crm\services\webmaster;

use common\models\webmaster\WmCheckout;

class WmCheckoutService
{
    private function wmCheckoutQuery()
    {
        return WmCheckout::find()
            ->join('JOIN', 'user', 'wm_id = id')
            ->join('JOIN', 'wm_profile', 'wm_id = wm_id')->asArray()->all();
    }

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_field
     * @param null $sort_order
     * @return array|WmCheckout[]|\yii\db\ActiveRecord[]
     */
    public function getWmCheckoutsList($filters = [], $pagination = null, $sort_field = null, $sort_order = null)
    {
        $query = $this->wmCheckoutQuery();
        var_dump($query);
        die();
        $query->where(['WC.status' => WmCheckout::IN_PROCESSING]);

        if (isset($filters['wm'])) $query->andWhere(['LIKE', 'U.username', $filters['wm']['value']]);
        if (isset($filters['card'])) $query->andWhere(['WP.card' => $filters['card']['value']]);
        if (isset($filters['amount'])) $query->andWhere(['WC.amount' => $filters['amount']['value']]);
        if (isset($filters['status'])) $query->andWhere(['WC.status' => $filters['status']['value']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'WC.created_at', $start_date]);
            $query->andWhere(['<', 'WC.created_at', $end_date]);
        }

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy([
                'created_at' => SORT_ASC
            ]);
        }

        $count = clone $query;
        $count_all = $count->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $result = [
            'checkouts' => $query
                ->asArray()
                ->all(),
            'count' => [
                'count_all' => $count_all,
            ],
        ];

        foreach ($result['checkouts'] as $key => $checkout) {
            $result['checkouts'][$key] = [
                'created_at' => $checkout['created_at'],
                'wm_checkout_id' => $checkout['wm_checkout_id'],
                'amount' => $checkout['amount'],
                'card' => $checkout['card'],
                'username' => $checkout['username'],
            ];
        }

        return $result;
    }

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_field
     * @param null $sort_order
     * @return array
     */
    public function getWmCheckoutsHistory($filters = [], $pagination = null, $sort_field = null, $sort_order = null)
    {
        $statuses = [
            WmCheckout::PAID_OUT,
            WmCheckout::REJECTED,
            WmCheckout::IN_PROCESSING,
        ];

        $query = $this->wmCheckoutQuery();

        $query->where(['in', 'WC.status', $statuses]);

        if (isset($filters['wm'])) $query->andWhere(['LIKE', 'U.username', $filters['wm']['value']]);
        if (isset($filters['card'])) $query->andWhere(['WP.card' => $filters['card']['value']]);
        if (isset($filters['amount'])) $query->andWhere(['WC.amount' => $filters['amount']['value']]);
        if (isset($filters['status'])) $query->andWhere(['WC.status' => $filters['status']['value']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'WC.created_at', $start_date]);
            $query->andWhere(['<', 'WC.created_at', $end_date]);
        }

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy([
                'created_at' => SORT_ASC
            ]);
        }

        $count = clone $query;
        $count_all = $count->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $result = [
            'checkouts_history' => $query
                ->asArray()
                ->all(),
            'count' => [
                'count_all' => $count_all,
            ],
        ];

        foreach ($result['checkouts_history'] as $key => $checkout) {
            $result['checkouts_history'][$key] = [
                'created_at' => $checkout['created_at'],
                'wm_checkout_id' => $checkout['wm_checkout_id'],
                'status' => WmCheckout::statusLabels($checkout['status']),
                'amount' => $checkout['amount'],
                'card' => $checkout['card'],
                'username' => $checkout['username'],
            ];
        }

        return $result;
    }

    /**
     * @param $id
     * @param $status
     * @param null $post
     * @return array|bool
     */
    public function changeStatus($id, $status, $post = null)
    {
        $wm_checkout = WmCheckout::findOne($id);
        $comment = empty($post['reason']) ? 'The administrator did not specify the reason' : $post['reason'];

        if ($status == WmCheckout::REJECTED) {
            $wm_checkout->setAttributes([
                'status' => WmCheckout::REJECTED,
                'comment' => $comment,
            ]);
        }

        $wm_checkout->setAttribute('status', $status);
        return $wm_checkout->validate() ? $wm_checkout->save() : $wm_checkout->errors;
    }
}