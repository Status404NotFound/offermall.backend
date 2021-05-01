<?php

namespace common\models;

class Instrument
{
    const DEFAULT = 1;

    /** Callcenter constants */
    const CALL_CENTER = 2;
    const CALL_CENTER_COMMENT = 21;
    const CALL_CENTER_TAKE_LEAD = 22;
    const CALL_CENTER_PLAN_CALL = 23;
    const CALL_CENTER_MAKE_CALL = 24;
    const CALL_CENTER_SET_EMIRATE = 25;
    const CALL_CENTER_SET_SKU = 26;
    const CALL_CENTER_PLAN_DELIVERY = 27;
    const CALL_CENTER_CARD_REJECT = 28;
    const CALL_CENTER_CARD_NOT_VALID = 29;


    /** OrderSkuLog constants */
//    const CRM_ORDER_INFO = 50;
//    const CRM_WFD = 51;
//    const CRM_GROUP_SEARCH = 52;

    const LMC_CALL_CENTER = 3;
    const LMC_CALL_CENTER_FINE = 32;
    const LMC_DUPLICATE_WRONG_GEO = 33;

    const LMC_FINSTRIP_CLOSED_PERIOD = 54;
    const LMC_WFD_TO_DIP = 55;

    public static function instruments($instrument_id = null)
    {
        $instruments = [
            self::DEFAULT => 'Default',
            self::CALL_CENTER_COMMENT => 'CC Left comment',
            self::CALL_CENTER => 'Call Center',
            self::CALL_CENTER_TAKE_LEAD => 'CC Take Lead',
            self::CALL_CENTER_PLAN_CALL => 'CC Plan Call',
            self::CALL_CENTER_SET_EMIRATE => 'CC Set Emirate',
            self::CALL_CENTER_SET_SKU => 'CC Set SKU',
            self::CALL_CENTER_PLAN_DELIVERY => 'CC Plan delivery',
            self::CALL_CENTER_CARD_REJECT => 'CC Reject lead',
            self::CALL_CENTER_CARD_NOT_VALID => 'CC Not valid lead',
            self::LMC_DUPLICATE_WRONG_GEO => 'LMC Wrong GEO',
            self::LMC_FINSTRIP_CLOSED_PERIOD => 'LMC Closed Financial period page',
            self::LMC_WFD_TO_DIP => 'LMC Deliveries',
        ];
        return isset($instrument_id) ? $instruments[$instrument_id] : $instruments;
    }

    public static function actionsByInstrument()
    {
        return [
            self::DEFAULT => ' change by default ',
            self::CALL_CENTER_PLAN_CALL => ' add order to Plan List ',
            self::CALL_CENTER_TAKE_LEAD => ' take this order ',
            self::CALL_CENTER_COMMENT => ' left comment: ',
            self::CALL_CENTER => ' change at call center app part ',
            self::CALL_CENTER_MAKE_CALL => ' has made call ',
            self::CALL_CENTER_SET_EMIRATE => ' save emirate to ',
            self::CALL_CENTER_SET_SKU => ' set SKU to ',
            self::CALL_CENTER_PLAN_DELIVERY => ' plan delivery to ',
            self::CALL_CENTER_CARD_REJECT => ' reject lead ',
            self::CALL_CENTER_CARD_NOT_VALID => ' not valid lead ',
            self::LMC_DUPLICATE_WRONG_GEO => ' duplicate on wrong geo reason from ',
            self::LMC_FINSTRIP_CLOSED_PERIOD => ' closed financial period from ',
            self::LMC_WFD_TO_DIP => ' change order_status from ',
        ];
    }

    public static function instrumentCommentColor()
    {
        return [
            self::CALL_CENTER_COMMENT => '#000000',
            self::CALL_CENTER_TAKE_LEAD => '#00bba7',
            self::CALL_CENTER_MAKE_CALL => '#1279d1',
            self::CALL_CENTER_PLAN_CALL => '#ffa080',
            self::CALL_CENTER_SET_SKU => 'rgb(215, 82, 255)',
            self::CALL_CENTER_PLAN_DELIVERY => '#bb0087',
            self::CALL_CENTER_CARD_REJECT => '#bb0087',
            self::CALL_CENTER_CARD_NOT_VALID => '#bb0087',
        ];
    }

    public static function getInstrument($instrument_id)
    {
        $instrument_label = isset(self::instruments()[$instrument_id]) ?? self::instruments()[self::DEFAULT];
        return $instrument_label;
    }

    public static function getAction($instrument_id)
    {
        $action_text = isset(self::actionsByInstrument()[$instrument_id]) ? self::actionsByInstrument()[$instrument_id] : self::actionsByInstrument()[self::DEFAULT];
        return $action_text;
    }
}