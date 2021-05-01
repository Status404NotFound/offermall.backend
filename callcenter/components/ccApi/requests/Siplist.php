<?php
namespace callcenter\components\ccApi\requests;

use yii;
use app\models\ccApi\core\CcApi;

/**
 * Class Siplist
 * @package app\models\ccApi\requests
 */
class Siplist extends CcApi
{
    protected $api_key = 6;

    public $per_page;
    public $page;
    public $status;

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
            [['per_page', 'page', 'status' ], 'safe'],
        ];
    }

}
