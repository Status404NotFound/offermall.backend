<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\delivery\DeliveryApi;
use common\models\delivery\OrderDelivery;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\ValidateException;
use crm\services\import\ImportFactory;
use crm\services\import\ImportService;
use crm\services\import\ImportServiceException;
use Dompdf\Exception;
use \PHPExcel_IOFactory;

/**
 * Class ImportController
 * @package crm\modules\angular_api\controllers
 */
class ImportController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\import\Import';
    /**
     * @var ImportService
     */
    private $importService;

    /**
     * ExportController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->importService = new ImportService();
    }

    public function actionFetcher()
    {
        $result = $this->importService->makeImport(\Yii::$app->request->post('account_invoice_cod_report'));
//        $result = $this->importService->setBitrix(\Yii::$app->request->post('account_invoice_cod_report'));
        $this->response->data['success'] = $result['success'];
        $this->response->data['failed'] = $result['failed'];
        $this->response->send();
    }

    public function actionReports()
    {
        $files = scandir(\Yii::$app->basePath . '/services/import/fetcher_cod_reports/');
        $reports = [];
        foreach ($files as $key => $file) {
            if ($file == '.' || $file == '..') {
                unset($files[$key]);
                continue;
            }
            $reports[] = ['report_name' => $file];
        }
        $this->response->data = $reports;
        $this->response->send();
    }

    public function actionReport()
    {
        header('Access-Control-Allow-Origin: *');
        $file = file_get_contents(\Yii::$app->basePath . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'fetcher_cod_reports' . DIRECTORY_SEPARATOR . \Yii::$app->request->post('name'));
        $base64_string = base64_encode($file);
        $this->response->data = 'data:application/vnd.ms-excel;' . $base64_string;
        $this->response->send();
    }

    public function actionSkuName1c()
    {
        $result = $this->importService->Import_1c_sku(\Yii::$app->request->post('sku'));
        $this->response->data = $result;
        $this->response->send();
    }
}