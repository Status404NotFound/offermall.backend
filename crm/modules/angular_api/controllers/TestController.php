<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use yii\base\Module;
use common\services\contact\ContactSearchService;


class TestController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\order\Order';

    /**
     * @var ContactSearchService
     */
    private $contactSearchService;

    /**
     * ContactSearchController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->contactSearchService = new ContactSearchService();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
        ]);
        return $behaviors;
    }

    public function actionTest()
    {
        $arr= [1=>12, 2=>13, 3 => 14];
        $count = 0;

        foreach ($arr as $key => $value)
        {

            $count++;
            if (!empty($prev_key)){
                var_dump('Key: ' . $prev_key . 'Count: ' . $count);exit;
            }
            $prev_key = $key;
        }
    }
}