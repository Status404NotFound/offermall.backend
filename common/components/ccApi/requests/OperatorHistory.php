<?php
namespace common\components\ccApi\requests;

use yii;
use common\components\ccApi\core\CcApi;

/**
 * Class OperatorHistory
 * @package app\models\ccApi\requests
 */
class OperatorHistory extends CcApi
{
    protected $api_key = 7;
    public $sip;

    const STATUS_OFFLINE = 'offline';
    const STATUS_ONLINE = 'online';
    const STATUS_INUSE = 'inuse';

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            [['sip'], 'integer']
        ];
    }

}
