<?php

namespace crm\services\export;

use Yii;
use common\helpers\FishHelper;
use common\models\order\Order;
use common\services\order\OrderCommonService;
use common\models\order\OrderStatus;
use common\services\order\OrderNotFoundException;
use common\services\ValidateException;
use common\models\order\DeclarationPrint;
use common\models\delivery\UserRequisites;
use common\models\order\TaxInvoicePrint;
use Mpdf\Mpdf;

/**
 * Export XLS Documentation:
 * https://github.com/codemix/yii2-excelexport
 * Export PDF(Declaration and Tax Invoice with Arabic output) Documentation:
 * https://github.com/mpdf/mpdf
 *
 * Old solution Export PDF(Declaration) Documentation:
 * https://github.com/dompdf/dompdf
 *
 * Other variant of PDF export:
 * https://github.com/yii2tech/html2pdf
 */
class ExportService
{
    /**
     * Array of errors
     * @var array
     */
    public $errors = [];

    /**
     * @param $page
     * @param null $filters
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function getSheet($page, $filters = null)
    {
        $export = ExportFactory::createExport($page);
        $exportData = $export->compareData($filters);
        return ExportFactory::getExportSheet($exportData);
    }

    /**
     * @param array $order_id_array
     * @throws OrderNotFoundException
     * @throws \Mpdf\MpdfException
     */
    public function getDeclaration(array $order_id_array)
    {
        $orders = Order::find()->where(['in', 'order_id', $order_id_array])->all();

        if (empty($orders)) throw new OrderNotFoundException('Select orders you want to print Declaration.');

        $mpdf = new Mpdf(['tempDir' => __DIR__ . '/../../web/tmp']);
        $stylesheet = '';
        $declaration = '';
        foreach ($orders as $order) {
            try {
                $stylesheet .= Yii::$app->controller->renderFile('@app/modules/angular_api/views/export/declaration/style.css');
                $declaration .= Yii::$app->controller->renderPartial('@app/modules/angular_api/views/export/declaration/declaration', ['order' => $order]);

                $declarationPrint = new DeclarationPrint();
                $declarationPrint->setAttributes([
                    'order_id' => $order->order_id,
                    'declaration' => (string)$order->order_hash,
                    'printed_by' => Yii::$app->user->identity->getId(),
                ]);
                if (!$declarationPrint->save()) throw new ValidateException($declarationPrint->errors);
                (new OrderCommonService())->changeStatus($order, OrderStatus::DELIVERY_IN_PROGRESS,
                    ['declaration' => $order->order_hash]);

            } catch (ValidateException $e) {
                $this->errors['ValidateException'] = $e;
            } catch (\Exception $e) {
                $this->errors['Exception'] = $e;
            }
        }

        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($declaration, 2);
        header('Access-Control-Allow-Origin: *');
        $mpdf->Output();
    }

    /**
     * @param array $order_id_array
     * @throws OrderNotFoundException
     * @throws \Mpdf\MpdfException
     */
    public function getTaxInvoice(array $order_id_array)
    {
        $orders = Order::find()->where(['in', 'order_id', $order_id_array])->all();
        $owner = Yii::$app->user->identity->getOwnerId();

        if (empty($orders)) throw new OrderNotFoundException('Select orders you want to print Tax invoice.');

        $mpdf = new Mpdf(['tempDir' => __DIR__ . '/../../web/tmp']);
        $stylesheet = '';
        $tax_invoice = '';
        foreach ($orders as $order) {

            $owner_id = !is_null($owner) ? $owner : $order->orderData->owner_id;

            $template = UserRequisites::findOne(['user_id' => $owner_id, 'geo_id' => $order->customer->country_id]);
            if (empty($template)) throw new OrderNotFoundException('Requisites not found.');

            try {
                $stylesheet .= Yii::$app->controller->renderFile('@app/modules/angular_api/views/export/tax_invoice/style.css');
                $tax_invoice .= Yii::$app->controller->renderPartial('@app/modules/angular_api/views/export/tax_invoice/tax_invoice', ['order' => $order, 'template' => $template]);

                $taxInvoicePrint = new TaxInvoicePrint();
                $taxInvoicePrint->setAttributes([
                    'order_id' => $order->order_id,
                    'tax_invoice' => (string)$order->order_hash,
                    'printed_by' => Yii::$app->user->identity->getId(),
                ]);

                if (!$taxInvoicePrint->save()) throw new ValidateException($taxInvoicePrint->errors);

            } catch (ValidateException $e) {
                $this->errors['ValidateException'] = $e;
            } catch (\Exception $e) {
                $this->errors['Exception'] = $e;
            }
        }

        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($tax_invoice, 2);
        header('Access-Control-Allow-Origin: *');
        $mpdf->Output();
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}