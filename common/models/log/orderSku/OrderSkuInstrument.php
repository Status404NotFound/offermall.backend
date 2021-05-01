<?php

namespace common\models\log\orderSku;

use common\models\Instrument;

class OrderSkuInstrument extends Instrument
{
    /** Instruments (places where OrderSku can be changed). */
//    const CALLCENTER = 2; // CALLCENTER is in parent class Instrument
    const CRM_ORDERS = 50;
    const CRM_WFD = 51;
    const CRM_GROUP_SEARCH = 52;

    const TDS_ONLINE_PAYMENT = 80;

    public static function instruments($instrument_id = null)
    {
        $instruments = [
            self::CRM_ORDERS => 'CRM Orders page',
            self::CRM_WFD => 'CRM WFD page',
            self::CRM_GROUP_SEARCH => 'CRM CRM Group Search page',
            self::CALL_CENTER_SET_SKU => 'CC Order Card',
            self::TDS_ONLINE_PAYMENT => 'TDS Online Payment',
        ];
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
            self::CRM_GROUP_SEARCH => ' ## CRM CRM Group Search page.',
            self::CALL_CENTER_SET_SKU => '## CC Order Card',
            self::TDS_ONLINE_PAYMENT => '## TDS Online Payment',
        ];
    }

    /**
     * @param $instrument_id
     * @return bool
     * @throws OrderSkuInstrumentException
     */
    public static function findInstrument($instrument_id)
    {
        if (!$instrument_label = isset(self::instruments()[$instrument_id]))
            throw new OrderSkuInstrumentException('Instrument Not Found.');
        return $instrument_label;
    }

    public static function getAction($instrument_id)
    {
        $action_text = isset(self::actionsByInstrument()[$instrument_id]) ? self::actionsByInstrument()[$instrument_id] : self::actionsByInstrument()[self::DEFAULT];
        return $action_text;
    }
}