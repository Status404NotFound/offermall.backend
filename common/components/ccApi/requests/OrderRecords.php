<?php
namespace common\components\ccApi\requests;

use yii;
use common\components\ccApi\core\CcApi;

/**
 * Class OrderRecords
 * @package app\models\ccApi\requests
 */
class OrderRecords extends CcApi
{
    protected $api_key = 3;

    public $order_id;

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            [['order_id'], 'safe'],
        ];
    }


}
