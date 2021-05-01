<?php

namespace common\services\customer;

use Yii;
use common\models\customer\CustomerBlackList;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\models\customer\BlackListAttempt;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class CustomerBlackListService
 * @package common\services\customer
 */
class CustomerBlackListService
{
    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function getBlackList($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $query = CustomerBlackList::find()
            ->select([
                'customer_blacklist.customer_black_list_id',
                'customer_blacklist.ip',
                'customer_blacklist.phone',
                'customer_blacklist.email',
                'customer_blacklist.reason_id',
                'customer_blacklist.status_id',
                'customer_blacklist.is_active',
                'customer_blacklist.created_at',
            ])
            ->joinWith(['blacklistAttempts blacklist_attempt' => function (ActiveQuery $query) {
                $query->select([
                    'blacklist_attempt.customer_black_list_id',
                    'SUM(blacklist_attempt.attempts) as attempts'
                ]);
                $query->groupBy(['blacklist_attempt.customer_black_list_id']);
            }]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'customer_blacklist.created_at', $start_date]);
            $query->andWhere(['<', 'customer_blacklist.created_at', $end_date]);
        }

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy(['customer_blacklist.created_at' => SORT_DESC]);
        }

        $count = clone $query;
        $count_all = $count->groupBy(['customer_blacklist.customer_black_list_id'])->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $blacklist = $query
            ->groupBy(['customer_blacklist.customer_black_list_id'])
            ->asArray()
            ->all();

        foreach ($blacklist as $key => $value) {

            $blacklist[$key]['status_name'] = OrderStatus::attributeLabels($value['status_id']);
            $blacklist[$key]['reason_name'] = StatusReason::getReason($value['status_id'], $value['reason_id']);

            foreach ($value['blacklistAttempts'] as $attempts) {
                $blacklist[$key]['attempts'] = $attempts['attempts'];
            }

            if (empty($value['blacklistAttempts'])) $blacklist[$key]['attempts'] = (string)0;
            unset($blacklist[$key]['blacklistAttempts']);
        }

        return [
            'result' => $blacklist,
            'count' => [
                'count_all' => $count_all
            ],
        ];
    }

    /**
     * @param array $data
     * @return bool
     * @throws CustomerException
     */
    public function saveBlackListData(array $data)
    {
        $black_list = isset($data['customer_black_list_id']) ? CustomerBlackList::findOne(['customer_black_list_id' => $data['customer_black_list_id']]) : new CustomerBlackList();

        $black_list->setAttributes([
            'ip' => !empty($data['ip']) ? trim($data['ip']) : null,
            'phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            'email' => !empty($data['email']) ? trim($data['email']) : null,
            'status_id' => $data['status_id'],
            'reason_id' => $data['reason_id'],
        ]);

        if (!$black_list->save()) {
            throw new CustomerException('Error');
        }

        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function changeStatus(string $id): bool
    {
        $blacklist = CustomerBlackList::findOne(['customer_black_list_id' => $id]);

        if ($blacklist->getIsBlocked()) {
            return $blacklist->unblock($blacklist->status_id, $blacklist->reason_id);
        } else {
            return $blacklist->block($blacklist->status_id, $blacklist->reason_id);
        }
    }

    /**
     * @param string $id
     * @return false|int
     * @throws CustomerException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(string $id)
    {
        $blacklist = CustomerBlackList::findOne(['customer_black_list_id' => $id]);

        if (!$blacklist)
            throw new CustomerException('Customer doesn\'t exist');

        return $blacklist->delete();
    }

    /**
     * @param string $ip
     * @return array
     */
    public function checkCustomerIp(string $ip): array
    {
        $lists = CustomerBlackList::getCustomersInBlacklist();

        $block = null;
        $data = [];
        if (is_array($lists)) {
            foreach ($lists as $list) {

                if (!is_null($list['ip'])) {
                    $ip_arr = rtrim($list['ip']);

                    $ip_check_matches = 0;
                    $db_ip_split = explode(".", $ip_arr);
                    $this_ip_split = explode(".", $ip);

                    for ($i = 0; $i < 4; $i++) {
                        if ($this_ip_split[$i] == $db_ip_split[$i] or $db_ip_split[$i] == '*') {
                            $ip_check_matches += 1;
                        }

                    }

                    if ($ip_check_matches == 4) {
                        $block = $list['ip'];
                        $data = [
                            'customer_id' => $list['id'],
                            'status_id' => $list['status_id'],
                            'reason_id' => $list['reason_id'],
                        ];
                        break;
                    }
                }
            }
        }

        return [
            'block' => $block ?? null,
            'customer_id' => isset($block) ? $data['customer_id'] : null,
            'status_id' => isset($block) ? $data['status_id'] : null,
            'reason_id' => isset($block) ? $data['reason_id'] : null,
        ];
    }

    /**
     * @param string $type
     * @param string $mode
     * @return array
     */
    public function checkCustomerInformation(string $type, string $mode): array
    {
        $lists = CustomerBlackList::getCustomersInBlacklist();

        $block = null;
        $data = [];
        if (is_array($lists)) {
            foreach ($lists as $list) {

                if (!is_null($list[$type])) {
                    $list[$type] = str_replace('\*', '.*', preg_quote($list[$type], "#"));

                    if ($list[$type] and preg_match("#^{$list[$type]}$#i", $mode)) {
                        $block = $list[$type];
                        $data = [
                            'customer_id' => $list['id'],
                            'status_id' => $list['status_id'],
                            'reason_id' => $list['reason_id'],
                        ];
                    }
                }
            }
        }

        return [
            'block' => $block ?? null,
            'customer_id' => isset($block) ? $data['customer_id'] : null,
            'status_id' => isset($block) ? $data['status_id'] : null,
            'reason_id' => isset($block) ? $data['reason_id'] : null,
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function saveAttempts(array $data)
    {
        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $time->setTime($time->format("H"), 0, 0);
        $date = $time->format('Y-m-d H:i:s');

        $blacklist_attempts = BlackListAttempt::find()
            ->where(['customer_black_list_id' => $data['customer_id']])
            ->andWhere(['date' => $date])
            ->andWhere(['status_id' => $data['status_id']])
            ->andWhere(['reason_id' => $data['reason_id']])
            ->one();

        if ($blacklist_attempts) {
            $blacklist_attempts->attempts += 1;
        } else {
            $blacklist_attempts = new BlackListAttempt();
            $blacklist_attempts->customer_black_list_id = $data['customer_id'];
            $blacklist_attempts->date = $date;
            $blacklist_attempts->status_id = $data['status_id'];
            $blacklist_attempts->reason_id = $data['reason_id'];
            $blacklist_attempts->attempts = 1;
        }

        return $blacklist_attempts->save();
    }

    /**
     * @param string $ip
     * @return array
     */
    private function mask2cidr(string $ip): array
    {
        $mask = '255.255.255.0';
        $wcmask = long2ip(~ip2long($mask));
        $subnet = long2ip(ip2long($ip) & ip2long($mask));
        $bcast = long2ip(ip2long($ip) | ip2long($wcmask));

        return [
            'subnet' => $subnet,
            'bcast' => $bcast
        ];
    }
}