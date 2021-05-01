<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\finance\advert\AdvertMoney;
use common\services\ValidateException;
use crm\services\finance\FinanceService;
use yii\base\Exception;
use Yii;

/**
 * Class PartnerMoneyController
 * @package crm\modules\angular_api\controllers
 */
class PartnerMoneyController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\finance\Finance';
    /**
     * @var FinanceService $financeService
     */
    private $financeService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->financeService = new FinanceService();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'change-balance' => ['post'],
            'partner-balance' => ['get'],
        ]);
        return $behaviors;
    }

    public function actionPartnerBalance()
    {
        $advertMoney = AdvertMoney::findOne(['advert_id' => \Yii::$app->request->getBodyParam('advert_id')]);
        $this->response->data = $advertMoney->money;
        $this->response->send();
    }

    public function actionChangeBalance()
    {
        $advert_id = Yii::$app->request->getBodyParam('advert_id');
//        $advert_id = 6;
        $sum = Yii::$app->request->getBodyParam('sum');
//        $sum = 20000;
        $date = Yii::$app->request->getBodyParam('date');
//        $date = date("Y-m-d H:i:s");

        $tx = Yii::$app->db->beginTransaction();
        try {
            $balance = (new FinanceService())->changeBalance($advert_id, $sum, null, $date);
            $tx->commit();
        } catch (ValidateException $e) {
            $tx->rollBack();
            throw $e;
        } catch (Exception $e) {
            $tx->rollBack();
            throw $e;
        }
        $this->response->send();
    }
}