<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\finance\advert\AdvertMoney;
use common\models\finance\CurrencyRatePerDay;
use common\services\finance\BalanceService;
use common\services\finance\FinanceCommonException;
use common\services\ValidateException;
use crm\services\finance\FinanceService;
use crm\services\finance\FinanceServiceExcepton;
use Yii;
use yii\base\Exception;
use common\models\SmsActivation;
use yii\web\{
    HttpException, NotFoundHttpException, ServerErrorHttpException
};

/**
 * Class FinanceController
 * @package crm\modules\angular_api\controllers
 */
class FinanceController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\finance\Finance';
    /**
     * @var FinanceService $financeService
     */
    private $financeService;

    /**
     * @var BalanceService $balanceService
     */
    private $balanceService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->financeService = new FinanceService();
        $this->balanceService = new BalanceService();
    }

    public function actionChecks()
    {
        $geo_id = $this->filters['geo_id'] ?? [];
        $offer_id = $this->filters['offer_id'] ?? null;
        $advert_id = $this->filters['advert_id']['value'] ?? null;
        $date_start = $this->filters['date_start']['value'] ?? null;
        $date_end = $this->filters['date_end']['value'] ?? null;
        $this->response->data = $this->financeService->getChecks($advert_id, $date_start, $date_end, $geo_id, $offer_id);
        $this->response->send();
    }

    public function actionFunds()
    {
        $advert_id = $this->filters['advert_id']['value'] ?? null;
        $funds = $this->financeService->getFunds($advert_id, $this->getRequestPagination());
        $this->response->data['funds'] = $funds['funds'];
        $this->response->data['total'] = $funds['total'];
        $this->setPaginationHeaders($funds['count']);
        $this->response->send();
    }

    public function actionMonthBalance()
    {
        $advert_id = $this->filters['advert_id']['value'] ?? null;
        $month_balance = $this->financeService->getMonthBalance($advert_id, $this->getRequestPagination());
        $this->response->data = $month_balance['month_balance'];
        $this->setPaginationHeaders($month_balance['count']);
        $this->response->send();
    }

    public function actionCurrencyRate()
    {
        $date = Yii::$app->request->getBodyParam('date');
        $currency_rates = Yii::$app->request->getBodyParam('currency_rates');
        $tx = Yii::$app->db->beginTransaction();
        try {
            $this->financeService->saveCurrencyRates($date, $currency_rates);
            $tx->commit();
        } catch (FinanceServiceExcepton $e) {
            $tx->rollBack();
            throw $e;
        } catch (ValidateException $e) {
            $tx->rollBack();
            throw $e;
        } catch (Exception $e) {
            $tx->rollBack();
            throw $e;
        };
        $this->response->send();
    }

    public function actionGetCurrencyRate()
    {
        $curRatesPerDay = $this->financeService->getCurrencyRates($this->filters,
            $this->getRequestPagination(),
            $this->getRequestSortOrder());
        $this->response->data = $curRatesPerDay['rates'];
        $this->setPaginationHeaders($curRatesPerDay['count']);
        $this->response->send();
    }

    public function actionSendSms()
    {
        $response = Yii::$app->response;
        $post = Yii::$app->request->post();
        $response->data = $this->balanceService->sendVerification($post);
        $response->send();
    }

    public function actionVerifySms()
    {
        $response = Yii::$app->response;
        $post = Yii::$app->request->post();

        $data = [
            'code' => md5($post['code']),
            'user_id' => $post['user_id'],
        ];

        if (!SmsActivation::find()->where(['hash' => $data['code']])->andWhere(['user_id' => $data['user_id']])->exists()) {
            throw new FinanceCommonException('Wrong verification code!');
        }
        $response->send();
    }

    public function actionChangeBalance()
    {
        $response = Yii::$app->response;
        $post = Yii::$app->request->post();
        $response->data = $this->balanceService->changeBalance($post['user_id'], $post['sum'], $post['comment'], $post['date']);
        $response->send();
    }
}