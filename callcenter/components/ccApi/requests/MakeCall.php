<?php
namespace callcenter\components\ccApi\requests;

use yii;
use callcenter\components\ccApi\core\CcApi;

/**
 * Class MakeCall
 * @package app\models\ccApi\requests
 */
class MakeCall extends CcApi
{
    protected $api_key = 1;

    public $sip;            // operators sip number
    public $phone;          // clients phone number
    public $order_id;       // conversion hash
    public $external_key;   // unique id of call


    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            [['sip', 'phone', 'order_id', 'external_key'], 'safe']
        ];
    }


}
