<?php

namespace common\models\log\orderInfo;

use common\helpers\FishHelper;
use common\models\Instrument;
use Yii;

class OrderInfoInstrument extends Instrument
{
    /** Instruments (places where Order can be changed). */
//    const CALLCENTER = 2; // CALLCENTER is in parent class Instrument
    const CRM_ORDERS = 50;
    const CRM_WFD = 51;
    const CRM_GROUP_SEARCH = 52;
    const CRM_DELIVERY = 53;

    const TDS_ONLINE_PAYMENT = 80;
    const MY_LAND_CRM_POSTBACK = 111;

    const PARTNER_CRM = 100;

    public static function instruments($instrument_id = null)
    {
        $instruments = [
            self::CRM_ORDERS => 'CRM Orders page',
            self::CRM_WFD => 'CRM WFD page',
            self::CRM_GROUP_SEARCH => 'CRM Group Search page',
            self::CRM_DELIVERY => 'CRM Delivery page',
            self::TDS_ONLINE_PAYMENT => 'TDS Online Payment',
            self::MY_LAND_CRM_POSTBACK => 'My Land CRM Postback',
            self::PARTNER_CRM => 'Partner CRM',
        ];

        $instruments += parent::instruments();
        if (isset($instrument_id) && isset($instruments[$instrument_id])) {
            return $instruments[$instrument_id];
        } elseif (!isset($instrument_id)) {
            return $instruments;
        }
        return false;
    }

    public static function actionsByInstrument()
    {
        return [
            self::CRM_ORDERS => ' ## CRM Orders page.',
            self::CRM_WFD => ' ## CRM WFD page.',
            self::CRM_GROUP_SEARCH => ' ## CRM Group Search page.',
            self::CRM_DELIVERY => ' ## CRM Delivery page.',
            self::TDS_ONLINE_PAYMENT => '## TDS Online Payment',
            self::MY_LAND_CRM_POSTBACK => '## My Land CRM Postback',
            self::PARTNER_CRM => '## Partner CRM',
        ];
    }

    /**
     * @param $instrument_id
     * @return bool
     * @throws OrderInfoInstrumentException
     */
    public static function findInstrument($instrument_id)
    {
        if (!$instrument_label = isset(self::instruments()[$instrument_id]))
            throw new OrderInfoInstrumentException('Instrument Not Found.');
        return $instrument_label;
    }

    public static function getAction($instrument_id)
    {
        $action_text = isset(self::actionsByInstrument()[$instrument_id]) ? self::actionsByInstrument()[$instrument_id] : self::actionsByInstrument()[self::DEFAULT];
        return $action_text;
    }
}