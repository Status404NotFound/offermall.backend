<?php

namespace crm\services\finance;

use common\models\finance\advert\AdvertMoney;
use common\models\finance\advert\AdvertMoneyEntrance;
use common\models\finance\Currency;
use common\models\finance\CurrencyRatePerDay;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\modules\user\models\tables\User;
use common\services\ValidateException;
use Yii;
use yii\db\Expression;

class FinanceService
{
    /**
     * @param $advert_id
     * @param $sum
     * @param null $order_currency_id
     * @param null $date
     * @return float
     * @throws FinanceServiceExcepton
     * @throws ValidateException
     */
    public function changeBalance($advert_id, $sum, $order_currency_id = null, $date = null)
    {
        if (!$advertMoney = AdvertMoney::findOne(['advert_id' => $advert_id]))
            throw new FinanceServiceExcepton('There is NO ' . $advertMoney->advert->username . ' balance.');
        if ($order_currency_id && !$date) {
            $sum = $this->calculateCurrency($sum, $advertMoney->currency_id, $order_currency_id);
            $advertMoney->money -= $sum;
        } else {
            $this->createEntrance($advert_id, $sum, $advertMoney->money, $date);
            $advertMoney->money += $sum;
        }
        if (!$advertMoney->save()) throw new ValidateException($advertMoney->errors);
        return (double)$advertMoney->money;
    }

    public function getChecks($advert_id = null, $date_start = null, $date_end = null, array $geo_id = [], $offer_id = null): array
    {
        if ( !$date_start) {
            $date_start = '2017-09-01';
        }
        if ( !$date_end) {
            $date_end = date('Y-m-d');
        }
        if ( !$advert_id) {
            $checks = AdvertMoney::find()
                ->select(['U.username', 'ROUND(AM.money, 2) as balance', 'C.currency_name as currency'])
                ->from('advert_money AM, currency C, user U')
                ->andWhere('C.currency_id = AM.currency_id')
                ->andWhere('U.id = AM.advert_id');
            
            return $checks->asArray()->all();
        }

        $approved = "(" . OrderStatus::CANCELED . ",
        " . OrderStatus::NOT_PAID . ", 
        " . OrderStatus::WAITING_DELIVERY . ", 
        " . OrderStatus::DELIVERY_IN_PROGRESS . ", 
        " . OrderStatus::SUCCESS_DELIVERY . ", 
        " . OrderStatus::RETURNED . ")";

        $data_query = Order::find()
            ->select([
                //'user.id as user_id',
                //'user.username as user_name',
                'DATE(`order`.created_at) as date',
                '`order`.offer_id',
                'offer.offer_name',
                'COUNT(`order`.order_id) as leads_count',
                'sum(`order`.total_amount) as orders_sku_total_amount',
                '-SUM(`order`.advert_commission) as orders_total_cost',
                '-SUM(`order`.usd_advert_commission) as usd_orders_total_cost',
                "SUM(if(`order`.order_status != ". OrderStatus::NOT_VALID_CHECKED .", 1, 0)) as valid",
                'target_advert_group.base_commission as commission',
                'advert_money.currency_id',
                'currency.currency_name as currency'])
            ->innerJoin('offer', '`order`.offer_id = offer.offer_id')
            ->innerJoin('target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->innerJoin('target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->innerJoin('advert_offer_target', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            //->innerJoin('user', 'user.id=' . $advert_id)
            ->innerJoin('advert_money', 'advert_money.advert_id=' . $advert_id)
            ->innerJoin('currency', 'currency.currency_id = advert_money.currency_id')
//            ->where('`order`.order_status >= advert_offer_target.advert_offer_target_status')
//            Start NEW code
            ->where('`order`.order_status >= advert_offer_target.advert_offer_target_status')
            ->andWhere("advert_offer_target.advert_offer_target_status != ". OrderStatus::WAITING_DELIVERY ."")
            ->orWhere("advert_offer_target.advert_offer_target_status = ". OrderStatus::WAITING_DELIVERY ." AND `order`.order_status IN ". $approved ."")
//            End new code
            ->andWhere(['target_advert.advert_id' => $advert_id])
            ->andWhere(['between', 'DATE(`order`.created_at)', $date_start, $date_end]);
        //->andWhere(['order.deleted' => 0])
    
        if ($offer_id) {
            $data_query->andWhere(['advert_offer_target.offer_id' => $offer_id]);
        }
        if ($geo_id) {
            $data_query->andWhere(['advert_offer_target.geo_id' => $geo_id]);
        }
    
        $data = $data_query
            ->groupBy(['date', 'offer_id', 'currency_id'])
            ->orderBy(['order.created_at' => SORT_ASC])
            ->asArray()
            ->all();
        
        $not_valid = Order::find()
            ->select([
                'DATE(order.created_at) as date',
                'order.offer_id',
                'advert_money.currency_id',
                "SUM(order.order_status = ". OrderStatus::NOT_VALID_CHECKED .") as not_valid"])
            ->innerJoin('offer', '`order`.offer_id = offer.offer_id')
            ->innerJoin('target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->innerJoin('target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->innerJoin('advert_offer_target', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->innerJoin('advert_money', 'advert_money.advert_id=' . $advert_id)
            ->innerJoin('currency', 'currency.currency_id = advert_money.currency_id')
            ->where("order.order_status = ". OrderStatus::NOT_VALID_CHECKED ."")
            ->andWhere(['target_advert.advert_id' => $advert_id])
            ->andWhere(['between', 'DATE(order.created_at)', $date_start, $date_end])
            ->groupBy(['date', 'offer_id', 'currency_id'])
            ->orderBy(['order.created_at' => SORT_ASC])
            ->asArray()
            ->all();


        $sorted_not_valid = [];
        foreach ($not_valid as $record) {
            $sorted_not_valid[$record['date']][$record['offer_id']][$record['currency_id']] = $record;
        }
        
        foreach ($data as &$record) {
            $record['not_valid'] = $sorted_not_valid[$record['date']][$record['offer_id']][$record['currency_id']]['not_valid'] ?? 0;
        }

//        print_r($data);die;

        return $this->_normalizeChecksResult($data, $advert_id, $this->getFunds($advert_id)['funds']);
    }

    private function _normalizeChecksResult($dateOffers, $advert_id, $funds): array
    {
        $result = [];
        $balance = 0;
        $user = User::find()->select('username')->where(['id' => $advert_id])->asArray()->one();
        //$advertMoney = AdvertMoney::findOne(['advert_id' => $advert_id]);

        foreach ($dateOffers as $dateOffer) {
            foreach ($funds as $key => $fund) {
    
                if (strtotime($fund['date']) <= strtotime($dateOffer['date'])) {
                    $fund['username'] = $user['username'];
                    $balance += round($fund['sum'], 2);
                    $fund['balance'] = round($balance, 2);
                    $result[] = $fund;
                    unset($funds[$key]);
                }
            }
    
            $dateOffer['total'] = round($dateOffer['orders_total_cost'], 2);
            $balance += round($dateOffer['total'], 2);
            $dateOffer['balance'] = round($balance, 2);
            $result[] = $dateOffer;
        }

        return array_reverse($result);
    }

    public function calculateCurrency($sum, $a_cur_id, $o_cur_id)
    {
        /** NO CALC */
        if ($a_cur_id === $o_cur_id) {
            return $sum;
        } /** CALC TO USD */
        elseif ($a_cur_id == Currency::USD) {
            $o_cur_rate = (double)CurrencyRatePerDay::getCurrencyRate($o_cur_id);
            $sumUSD = $sum / $o_cur_rate;
            return $sumUSD;
        }
        /** CALC ORDER TO USD AND TO ADVERT_CURRENCY */
        //        $order_cur_rate = CurrencyRatePerDay::getCurrencyRate($order_currency_id);
        //        $sumUSD = $sum / $order_cur_rate;
        //        $advert_cur_rate = CurrencyRatePerDay::getCurrencyRate($advertMoney->currency_id);
        //        return $sumUSD * $advert_cur_rate;
        return (double)($sum / CurrencyRatePerDay::getCurrencyRate($o_cur_id)) * CurrencyRatePerDay::getCurrencyRate($a_cur_id);
    }
    
//    private function _normalizeChecksResult___OLD($query, $advert_id, $userFunds)
//    {
//        $result = [];
//        $advertMoney = AdvertMoney::findOne(['advert_id' => $advert_id]);
//
//        foreach ($userFunds as $fund) {
//            $fund['total'] = $fund['sum'];
//            $fund['currency'] = $fund['currency_name'];
//            $user = User::find()->select('username')->where(['id' => $advert_id])->asArray()->one();
//            $fund['username'] = $user['username'];
//
//            if (isset($result[$fund['date']])) {
//                $result[$fund['date']][] = $fund;
//            } else {
//                $result[$fund['date']] = [];
//                $result[$fund['date']][] = $fund;
//            }
//        }
//
//
//        foreach ($query as &$dateOffer) {
//            $nearestFund = AdvertMoneyEntrance::find()
//                ->select(['DATE(entrance_date) AS date', 'sum', 'old_sum'])
//                ->where(['<=', 'DATE(entrance_date)', $dateOffer['date']])
//                ->asArray()
//                ->one();
//
//
//            $sum_between = 0;
//            foreach ($query as $date) {
//                if (isset($date['orders_total_cost'])
//                    && strtotime($date['date']) <= strtotime($dateOffer['date'])
//                    && strtotime($date['date']) >= strtotime($nearestFund['date'])
//                ) {
//                    $sum_between += $date['orders_total_cost'];
//                }
//            }
//
//            $dateOffer['total'] = $this->calculateCurrency($dateOffer['orders_total_cost'], $advertMoney->currency_id, $dateOffer['currency_id']);
//            unset($dateOffer['orders_total_cost']);
//
//            $last_balance = $nearestFund['sum'] + $nearestFund['old_sum'];
//            $dateOffer['balance'] = $last_balance + $sum_between;
//
//            if (!isset($result[$dateOffer['date']])) {
//                $result[$dateOffer['date']] = [];
//                $result[$dateOffer['date']][] = $dateOffer;
//            } else {
//                $result[$dateOffer['date']][] = $dateOffer;
//            }
//        }
//
//        $res = [];
//        krsort($result);
//        foreach ($result as $val) {
//            foreach ($val as $row) {
//                $res[] = $row;
//            }
//        }
//        return $res;
//    }

    public function getMonthBalance($advert_id, $pagination = null)
    {
        if ($advert_id === null) throw new FinanceServiceExcepton('No advert (ID) - no money.');
        /** @var User $user */
        $user = Yii::$app->user->identity;
//        if ($user->role !== User::ROLE_ADMIN && $user->id != $advert_id) {
//            throw new FinanceServiceExcepton('You have no access to this page.');
//        } elseif (!$advert_id && $user->role === User::ROLE_ADMIN) {
        if (!$advert_id) {
            $month_balance = AdvertMoney::find()
                ->select(['U.username', 'AM.money as balance', 'C.currency_name as currency'])
                ->from('advert_money AM, currency C, user U')
                ->andWhere('C.currency_id = AM.currency_id')
                ->andWhere('U.id = AM.advert_id');
            $count = clone $month_balance;
            $count = $count->count();
            $result['checks'] = $month_balance->offset($pagination['first_row'])
                ->limit($pagination['rows'])
                ->asArray()->all();
            $result['count'] = $count;
        } else {
            $query = Yii::$app->db->createCommand('CALL advert_finance_month_balance_paginate(:AdvertId, :FirstRow, :PageRows)')
                ->bindValue(':AdvertId', $advert_id)
                ->bindValue(':FirstRow', $pagination['first_row'])
                ->bindValue(':PageRows', $pagination['rows'])
                ->queryAll();
//            $result ['month_balance'] = $query;
            $result ['month_balance'] = $this->_normalizeMonthBalanceResult($query);

            $cnt_month_balance = Yii::$app->db->createCommand('CALL advert_finance_month_balance(:AdvertId)')
                ->bindValue(':AdvertId', $advert_id)
                ->queryAll();
            $result ['count'] = count($cnt_month_balance);
        }
        return $result;
    }

    private function _normalizeMonthBalanceResult($query)
    {
        foreach ($query as &$month) {
            if (!isset($month['total_down']) || empty($month['total_down']))
                $month['total_down'] = 0.0;
            if (!isset($month['total_up']) || empty($month['total_up']))
                $month['total_up'] = 0.0;
            $month['total'] = $month['total_up'] + $month['total_down'];
        }
        return $query;
    }

    public function getFunds($advert_id, $pagination = null)
    {
        if ($advert_id === null) throw new FinanceServiceExcepton('No advert (ID) - no money.');
        $query = AdvertMoneyEntrance::find()
            ->select([
                'advert_id',
                'DATE(entrance_date) as date',
                'sum',
                'comment',
                'old_sum'
            ])
            ->where(['advert_id' => $advert_id])
            ->orderBy(['date' => SORT_DESC]);
        $count = clone $query;
        $count = $count->count();
        if (!$pagination) {
            $advertFunds = $query->asArray()->all();
        } else {
            $advertFunds = $query->offset($pagination['first_row'])->limit($pagination['rows'])->asArray()->all();
        }
        /** @var AdvertMoney $advertMoney */
        $advertMoney = AdvertMoneyEntrance::getAdvertMoney($advert_id);
        foreach ($advertFunds as &$advertFund) {
            /** @var AdvertMoneyEntrance $advertFund */
            $advertFund['balance'] = $advertFund['old_sum'] + $advertFund['sum'];
            $advertFund['total'] = $advertFund['sum'];
            $advertFund['currency_id'] = $advertMoney->currency_id;
            $advertFund['currency_name'] = $advertMoney->currency->currency_name;
            $advertFund['currency'] = $advertFund['currency_name'];
        }
        $total = AdvertMoneyEntrance::find()
            ->where(['advert_id' => $advert_id])
            ->sum('sum');
        
        return ['funds' => $advertFunds, 'count' => $count, 'total' => $total . ' ' . $advertMoney->currency->currency_name];
    }

    public function createEntrance($advert_id, $sum, $old_sum, $date)
    {
        $moneyEntrance = new AdvertMoneyEntrance();
        $moneyEntrance->setAttributes([
            'advert_id' => $advert_id,
            'old_sum' => $old_sum,
            'sum' => $sum,
            'added_by' => Yii::$app->user->identity->getId(),
            'entrance_date' => (string)$date
        ]);
        if (!$moneyEntrance->save()) throw new ValidateException($moneyEntrance->errors);
    }

    public function saveCurrencyRates($date, $currency_rates)
    {
        if (empty($currency_rates)) throw new FinanceServiceExcepton('The rates are not set.');
        $distinct_currencies = CurrencyRatePerDay::find()
            ->distinct('currency_id')
            ->indexBy('currency_id')
            ->asArray()->all();
        foreach ($currency_rates as $currency) {
            $curRatePerDay = CurrencyRatePerDay::find()
                    ->where(['currency_id' => $currency['currency_id']])
                    ->andWhere(['DATE(date)' => $date])
                    ->one() ?? new CurrencyRatePerDay();
            $curRatePerDay->currency_id = $currency['currency_id'];
            $curRatePerDay->rate = ($currency['rate'] != null) ? $currency['rate'] : $this->getOldestRate($currency['currency_id']);
            $curRatePerDay->date = $date;
            if (!$curRatePerDay->save()) throw new ValidateException($curRatePerDay->errors);
            unset($distinct_currencies[$currency['currency_id']]);
        }
        if (!empty($distinct_currencies)) {
            foreach ($distinct_currencies as $cur_id => $cur) {
                $curRatePerDay = new CurrencyRatePerDay();
                $curRatePerDay->currency_id = $cur_id;
                $curRatePerDay->rate = $this->getOldestRate($cur_id);
                $curRatePerDay->date = $date;
                if (!$curRatePerDay->save()) throw new ValidateException($curRatePerDay->errors);
            }
        }
    }

    public function getCurrencyRates($filters, $pagination, $sort_order)
    {
        $query = CurrencyRatePerDay::find()
            ->select([
                'CR.rate',
                'CR.currency_id',
                'DATE(CR.date) as date',
                'C.currency_name',
                'CN.country_code',
            ])
            ->from('currency_rate_per_date CR, currency C, countries CN')
            ->andWhere('C.currency_id = CR.currency_id AND CN.id = C.country_id');

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'CR.date', $start_date]);
            $query->andWhere(['<', 'CR.date', $end_date]);
        }

        $count = clone $query;
        $count = $count->select('DAY(CR.date)')->distinct()->count();

        $currency_count = clone $query;
        $currency_count = $currency_count->select('CR.currency_id')->distinct()->count() - 1;

        $query_result = $query
            ->andWhere(['NOT IN', 'CR.currency_id', [1]])// USD
            ->orderBy([
                'CR.date' => SORT_DESC,
                'CR.currency_id' => SORT_ASC
            ])
            ->offset($pagination['first_row'] * $currency_count)->limit($pagination['rows'] * $currency_count)
            ->asArray()->all();


        $rates = [];
        foreach ($query_result as $rate) {
            if (isset($rates[$rate['date']])) {
                $rates[$rate['date']] = array_merge($rates[$rate['date']], [$rate['currency_name'] => [
                    'currency_id' => $rate['currency_id'],
                    'iso' => $rate['country_code'],
                    'rate' => (double)$rate['rate']]]);
            } else {
                $rates[$rate['date']] = [$rate['currency_name'] => [
                    'currency_id' => $rate['currency_id'],
                    'iso' => $rate['country_code'],
                    'rate' => (double)$rate['rate']]];
            }
        }

        $curRatesPerDay['rates'] = $rates;
        $curRatesPerDay['count'] = $count;
        return $curRatesPerDay;
    }

    private function getOldestRate($currency_id)
    {
        $max_cur_date = CurrencyRatePerDay::find()
            ->where(['currency_id' => $currency_id])
            ->max('date');

        $rate = CurrencyRatePerDay::find()
            ->where([
                'currency_id' => $currency_id,
                'date' => $max_cur_date
            ])
            ->one();
        return $rate->rate;
    }
}