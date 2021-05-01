<?php
namespace callcenter\components\ccApi\requests;

use yii;
use callcenter\components\ccApi\core\CcApi;

/**
 * Class RecordList
 * @package app\models\ccApi\requests;
 */
class RecordList extends CcApi
{
    protected $api_key = 2;

    public $page;
    public $per_page;

    public function rules()
    {
        return [
            [['page', 'per_page'], 'safe'],
        ];
    }


    public function init()
    {
        parent::init();
    }


}
