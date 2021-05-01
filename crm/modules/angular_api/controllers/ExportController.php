<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderCommonService;
use common\helpers\FishHelper;
use common\models\order\DeclarationPrint;
use common\models\order\TaxInvoicePrint;
use common\models\delivery\UserRequisites;
use common\services\order\OrderNotFoundException;
use common\services\ValidateException;
use crm\services\export\ExportService;
use Dompdf\Dompdf;
use yii\base\Exception;

/**
 * Class ExportController
 * @package crm\modules\angular_api\controllers
 */
class ExportController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\export\Export';
    /**
     * @var ExportService
     */
    private $exportService;

    /**
     * ExportController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->exportService = new ExportService();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'declaration' => ['post'],
            'orders' => ['post'],
        ]);
        return $behaviors;
    }

    public function actionDeclaration()
    {
        $order_id_array = Yii::$app->request->getBodyParam('orders');
        $declaration = $this->exportService->getDeclaration($order_id_array);
        $this->response->data = $declaration;
        $this->response->send();
    }

    public function actionTaxInvoice()
    {
        $order_id_array = Yii::$app->request->getBodyParam('orders');
        $tax = $this->exportService->getTaxInvoice($order_id_array);
        $this->response->data = $tax;
        $this->response->send();
    }

    public function actionOrders()
    {
        $page = Yii::$app->request->getBodyParam('page');
        $filters = $this->getRequestFilters();
        $filters['order_id_array'] = Yii::$app->request->getBodyParam('orders');

        $sheet = $this->exportService->getSheet($page, $filters);

        header('Access-Control-Allow-Origin: *');
        $path = $this->module->getBasePath() . '/views/export/orders/';
        $sheet->send($path . "template.xlsx");
    }

    public function actionGroupSearch()
    {
        $page = Yii::$app->request->getBodyParam('page');
        $filters = $this->getRequestFilters();

        $sheet = $this->exportService->getSheet($page, $filters);

        header('Access-Control-Allow-Origin: *');
        $path = $this->module->getBasePath() . '/views/export/orders/';
        $sheet->send($path . "fs_template.xlsx");
    }

    public function actionFinstrip()
    {
        $page = Yii::$app->request->getBodyParam('page');
        $filters = $this->getRequestFilters();

        $sheet = $this->exportService->getSheet($page, $filters);

        header('Access-Control-Allow-Origin: *');
        $path = $this->module->getBasePath() . '/views/export/orders/';
        $sheet->send($path . "fs_template.xlsx");
    }

    public function actionFinstripSummary()
    {
        $page = Yii::$app->request->getBodyParam('page');
        $filters = $this->getRequestFilters();

        $sheet = $this->exportService->getSheet($page, $filters);

        header('Access-Control-Allow-Origin: *');
        $path = $this->module->getBasePath() . '/views/export/orders/';
        $sheet->send($path . "fs_template.xlsx");
    }

    public function actionSuccessOrders()
    {
        $page = Yii::$app->request->getBodyParam('page');
        $filters = $this->getRequestFilters();
        $filters['order_id_array'] = Yii::$app->request->getBodyParam('orders');

        $sheet = $this->exportService->getSheet($page, $filters);

        header('Access-Control-Allow-Origin: *');
        $path = $this->module->getBasePath() . '/views/export/orders/';
        $sheet->send($path . "template.xlsx");
    }

    //    public function actionDeclaration()
//    {
//        // TODO: Rebase logic into service
//        $order_id_array = Yii::$app->request->getBodyParam('orders');
//        $orders = Order::find()->where(['in', 'order_id', $order_id_array])->all();
//        if (empty($orders)) throw new OrderNotFoundException('Select orders you want to print Declaration.');
//        $dompdf = new Dompdf();
//        $declaration = $this->renderPartial('declaration/style');
//        foreach ($orders as $order) {
//            try {
//                $declaration .= $this->renderPartial('declaration/declaration', ['order' => $order]);
//
//                $declarationPrint = new DeclarationPrint();
//                $declarationPrint->setAttributes([
//                    'order_id' => $order->order_id,
//                    'declaration' => (string)$order->order_hash,
//                    'printed_by' => Yii::$app->user->identity->getId(),
//                ]);
//                if (!$declarationPrint->save()) throw new ValidateException($declarationPrint->errors);
//                (new OrderCommonService())->changeStatus($order, OrderStatus::DELIVERY_IN_PROGRESS,
//                    ['declaration' => $order->order_hash]);
//
//            } catch (ValidateException $e) {
//                $this->result->addMessage($e->getMessage(), $e->getName());
//            } catch (Exception $e) {
//                $this->result->addMessage($e->getMessage(), $e->getName());
//            }
//        }
//        $declaration .= '</body>';
//
//        $dompdf->loadHtml($declaration);
//// (Optional) Setup the paper size and orientation
//        $dompdf->setPaper('A4');
//// Render the HTML as PDF
//        $dompdf->render();
//
//        header('Access-Control-Allow-Origin: *');
//// Output the generated PDF to Browser
//        $dompdf->stream();
//
////        $this->result->putData('success_sheet', $success_sheet);
////        $this->result->putData('break_sheet', $break_sheet);
////        $this->response->data['data'] = $this->result->getData();
////        $this->response->data['messages'] = $this->result->getMessages();
////        $this->response->send();
//    }

//    public function actionTaxInvoice()
//    {
//        $order_id_array = Yii::$app->request->getBodyParam('orders');
//        $orders = Order::find()->where(['in', 'order_id', $order_id_array])->all();
//
//        if (empty($orders)) throw new OrderNotFoundException('Select orders you want to print Tax invoice.');
//
//        $dompdf = new Dompdf();
//        $tax_invoice = $this->renderPartial('tax_invoice/style');
//        foreach ($orders as $order) {
//
//            if (is_null($order->orderData->owner_id))
//                throw new UserServiceException('Owner not found in order: #' . $order->order_hash);
//
//            if (!$tax_invoice_file = self::$tax_invoice_by_owner_id[$order->orderData->owner_id])
//                throw new UserServiceException('Owner in order: #' . $order->order_hash . 'doesn\'t have tax invoice');
//
//            try {
//                $tax_invoice .= $this->renderPartial("tax_invoice/$tax_invoice_file", ['order' => $order]);
//
//                $taxInvoicePrint = new TaxInvoicePrint();
//                $taxInvoicePrint->setAttributes([
//                    'order_id' => $order->order_id,
//                    'tax_invoice' => (string)$order->order_hash,
//                    'printed_by' => Yii::$app->user->identity->getId(),
//                ]);
//
//                if (!$taxInvoicePrint->save()) throw new ValidateException($taxInvoicePrint->errors);
//
//            } catch (ValidateException $e) {
//                $this->result->addMessage($e->getMessage(), $e->getName());
//            } catch (Exception $e) {
//                $this->result->addMessage($e->getMessage(), $e->getName());
//            }
//        }
//
//        $tax_invoice .= '</body>';
//
//        $dompdf->loadHtml($tax_invoice);
//        // (Optional) Setup the paper size and orientation
//        $dompdf->setPaper('A4');
//        // Render the HTML as PDF
//        $dompdf->render();
//
//        header('Access-Control-Allow-Origin: *');
//        // Output the generated PDF to Browser
//        $dompdf->stream();
//    }
}