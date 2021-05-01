<?php
namespace common\services\finance;

use Yii;
use common\models\finance\advert\AdvertMoney;
use common\models\finance\advert\AdvertMoneyEntrance;
use common\models\finance\Currency;
use common\models\finance\CurrencyRatePerDay;
use common\services\ValidateException;

class FinanceCommonService
{
    /**
     * @param $advert_id
     * @param $sum
     * @param null $order_currency_id
     * @param null $date
     * @return float
     * @throws FinanceCommonException
     * @throws ValidateException
     */
    public function changeBalance($advert_id, $sum, $order_currency_id = null, $date = null)
    {
        if (!$advertMoney = AdvertMoney::findOne(['advert_id' => $advert_id]))
            throw new FinanceCommonException('There is NO ' . $advertMoney->advert->username . ' balance.');
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

    /**
     * @param $sum
     * @param $a_cur_id - Advert Currency Id
     * @param $o_cur_id - Order Currency Id
     * @return float
     */
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

    /**
     * @param $advert_id
     * @param $sum
     * @param $old_sum
     * @param $date
     * @throws ValidateException
     */
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
}