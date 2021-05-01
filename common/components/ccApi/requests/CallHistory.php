<?php
namespace common\components\ccApi\requests;

use yii;
use common\components\ccApi\core\CcApi;

/**
 * Class CallHistory
 * @package app\models\ccApi\requests
 */
class CallHistory extends CcApi
{
    protected $api_key = 5;

    public $call_id;

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            [['call_id'], 'safe']
        ];
    }

}
