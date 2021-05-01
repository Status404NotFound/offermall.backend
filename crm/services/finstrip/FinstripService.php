<?php

namespace crm\services\finstrip;

use Yii;
use common\models\Instrument;
use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\models\finance\ClosedFinancialPeriods;
use common\services\ValidateException;
use crm\models\finstrip\calendar\FinstripDayOffer;
use crm\models\finstrip\calendar\FinstripDayOfferGeo;
use crm\models\finstrip\calendar\FisntripMonth;
use crm\models\finstrip\DayOfferGeoSubCost;
use crm\models\finstrip\calendar\FinstripCalendar;
use crm\models\finstrip\calendar\FisntripDay;
use crm\models\finstrip\offer\FisntripOffer;
use crm\models\finstrip\offer\FisntripOfferDay;
use crm\models\finstrip\offer\FisntripOfferGeo;
use crm\models\finstrip\offer\FisntripOfferMonth;
use crm\models\finstrip\offer\FisntripOffers;
use crm\models\finstrip\summary\FinstripSummaryMonth;
use crm\models\finstrip\summary\FinstripSummaryOfferDays;
use crm\models\finstrip\summary\FinstripSummaryOfferSub;
use crm\models\finstrip\summary\FinstripSummaryOfferGeo;
use crm\models\finstrip\summary\FinstripSummaryOffers;
use common\services\webmaster\Helper;
use common\models\SmsActivation;
use common\services\log\LogSrv;


/**
 * Class FinstripService
 * @package crm\services\finstrip
 */
class FinstripService
{
    /**
     * Finstrip Calendar methods
     */
    public function getCalendar($filters)
    {
        return (new FinstripCalendar())->searchCalendar($filters);
    }

    public function getMonth($filters)
    {
        return (new FisntripMonth())->searchMonth($filters);
    }

    public function getDay($filters)
    {
        return (new FisntripDay())->searchDay($filters);
    }

    public function getDayOffer($filters)
    {
        return (new FinstripDayOffer())->searchDayOffer($filters);
    }

    public function getDayOfferGeo($filters)
    {
        return (new FinstripDayOfferGeo())->searchDayOfferGeo($filters);
    }

    /**
     * Finstrip Offer methods
     */
    public function getOffers($filters)
    {
        return (new FisntripOffers())->searchOffers($filters);
    }

    public function getOffer($filters) // Geo
    {
        return (new FisntripOffer())->searchOffer($filters);
    }

    public function getOfferGeo($filters) // Months
    {
        return (new FisntripOfferGeo())->searchOfferGeo($filters);
    }

    public function getOfferMonth($filters) // Days
    {
        return (new FisntripOfferMonth())->searchOfferMonth($filters);
    }

    public function getOfferDay($filters) // Subs
    {
        return (new FisntripOfferDay())->searchOfferDay($filters);
    }

    /**
     * Finstrip Summary
     */
    public function getSummaryMonth($filters)
    {
        return (new FinstripSummaryMonth())->searchSummaryMonth($filters);
    }

    public function getSummaryOffers($filters)
    {
        return (new FinstripSummaryOffers())->searchSummaryOffer($filters);
    }

    public function getSummaryOffersGeo($filters)
    {
        return (new FinstripSummaryOfferGeo())->searchSummaryOfferGeo($filters);
    }

    public function getSummaryOfferDays($filters)
    {
        return (new FinstripSummaryOfferDays())->searchSummaryOfferDays($filters);
    }

    public function getSummaryOfferSub($filters)
    {
        return (new FinstripSummaryOfferSub())->searchSummaryOfferSub($filters);
    }

    public function listOfPeriods($filters, $pagination, $sort_field, $sort_order)
    {
        $query = ClosedFinancialPeriods::find()
            ->select([
//                'CONCAT(LAST_DAY(DATE_FORMAT(closed_financial_periods.date, "%Y-%m-%d" )), \' 23:59:59\') AS date',
                'CONCAT(DATE_SUB(LAST_DAY(DATE_FORMAT(closed_financial_periods.date, "%Y-%m-%d" )), INTERVAL 1 MONTH), \' 23:59:59\') AS date',
                'closed_financial_periods.created_at',
                'closed_financial_periods.created_by',
                'user.username'
            ])
            ->join('LEFT JOIN', 'user', 'user.id = closed_financial_periods.created_by');

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy(['closed_financial_periods.date' => SORT_DESC]);
        }

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $result = $query
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @param array $data
     * @return bool
     * @throws ValidateException
     */
    public function closedPeriod(array $data)
    {
        $period = new ClosedFinancialPeriods();

        $period->setAttribute('date', $data['date']);
        $log = new LogSrv($period, Instrument::LMC_FINSTRIP_CLOSED_PERIOD);

        if (!$period->save()) {
            var_dump($period->errors);
            return false;
        }

        $log->add();

        return true;
    }

    /**
     * @return bool
     * @throws ValidateException
     */
    public function generateSmsPassword()
    {
        $key = Helper::randomString(4, true);
        $code = md5($key);

        $sms = new SmsActivation();
        $sms->setAttributes([
            'user_id' => Yii::$app->user->identity->getId(),
            'hash' => $code,
        ]);

        if (!$sms->save()) throw new ValidateException($sms->errors);

        $message = 'Verification code for close period: ' . $key;
        $mega_admin_phone = Yii::$app->params['mega_admin_phone'];

        Yii::$app->serviceSms->send($message, $mega_admin_phone);

        return true;
    }

    /**
     * @return array|ClosedFinancialPeriods|null|\yii\db\ActiveRecord
     */
    public function getClosedPeriod()
    {
        return ClosedFinancialPeriods::find()
            ->select([
                'closed_financial_periods.date',
                'closed_financial_periods.updated_at',
                'closed_financial_periods.updated_by',
                'user.username'
            ])
            ->join('LEFT JOIN', 'user', 'user.id = closed_financial_periods.updated_by')
            ->orderBy(['closed_financial_periods.period_id' => SORT_DESC])
            ->asArray()
            ->one();
    }

    public function saveDayOfferGeoSubCost($sub)
    {
        $sub['currency_id'] = Currency::USD;
        $sub['rate'] = 1;
        $sub['sum'] = str_replace(',', '.', $sub['sum']);
        $sub['usd_sum'] = $sub['sum'];

        /** BEGIN If Fisnstrip will use currencies */
// if ($sub['currency_id == Currency::USD) {$sub['usd_sum = $sub['sum;$sub['rate = 1;}
// else {$sub['rate = CurrencyRatePerDay::getCurrencyRate($sub['currency_id);$sub['usd_sum = $sub['sum / $sub['rate;}
        /** END If Fisnstrip will use currencies */

        $dayOfferGeoSubCost = DayOfferGeoSubCost::findOne([
                'offer_id' => $sub['offer_id'],
                'date' => $sub['date'] . ' 00:00:00', // MySQL format
                'sub_id_1' => $sub['sub_id_1'],
                'known_sub_id' => $sub['known_sub_id'],
                'geo_id' => $sub['geo_id']
            ]) ?? new DayOfferGeoSubCost();

        $dayOfferGeoSubCost->setAttributes([
            'sub_id_1' => $sub['sub_id_1'],
            'known_sub_id' => $sub['known_sub_id'],
            'sum' => $sub['sum'],
            'currency_id' => $sub['currency_id'],
            'rate' => $sub['rate'],
            'usd_sum' => $sub['usd_sum'],
            'offer_id' => $sub['offer_id'],
            'geo_id' => $sub['geo_id'],
            'date' => $sub['date'],
        ]);
        if (!$dayOfferGeoSubCost->save()) {
            FishHelper::debug($dayOfferGeoSubCost->errors, 0);
            throw new ValidateException($dayOfferGeoSubCost->errors);
        }
    }
}