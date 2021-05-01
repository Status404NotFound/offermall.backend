<?php

namespace crm\services\import\logic;

use common\helpers\FishHelper;
use common\models\delivery\OrderDelivery;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\product\ProductSku;
use common\services\order\OrderCommonService;
use common\services\ValidateException;
use crm\services\import\ImportServiceException;
use Yii;

class SkuNames
{
    public function saveFile($base64_string)
    {
        $time = date('Y-m-d H:i:s');
//        $time = date('Y-m-d_H_i_s');
        $file_path = Yii::$app->basePath . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'fetcher_cod_reports' . DIRECTORY_SEPARATOR . $time . '.xls';
//        $file_path = Yii::$app->basePath . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'fetcher_cod_reports' . DIRECTORY_SEPARATOR . $time . '.xls';
        $base_to_php = explode(',', $base64_string);

        $data = base64_decode($base_to_php[1]);
        if (!file_put_contents($file_path, $data))
            throw new ImportServiceException('Failed to CREATE report file');

        chmod($file_path, 0777);
        return $file_path;
    }

    public function parseReport($file_path)
    {
        $file = \PHPExcel_IOFactory::createReader('Excel2007')
            ->load($file_path)
            ->getActiveSheet();

        $maxRow = $file->getHighestRow();

        $sku_names_1C = [];
        for ($row = 1; $row < $maxRow+1; $row++) {
//            $sku_common_name = preg_replace('/\s+/','',trim($file->getCell('A' . $row)->getValue()));
//            $sku_1C_code = preg_replace('/\s+/','',trim($file->getCell('B' . $row)->getValue()));

            $sku_common_name = trim(str_replace(" ", "", $file->getCell('A' . $row)->getValue()));
            $sku_1C_code = trim(str_replace(" ", "", $file->getCell('B' . $row)->getValue()));
            if (!isset($sku_names_1C[$sku_common_name])) $sku_names_1C[$sku_common_name] = $sku_1C_code;
            $sku_names_1C[$sku_common_name] = $sku_1C_code;
        }

        $success = [];
        $fail = [];

        foreach ($sku_names_1C as $sku_name => $sku_1c_code)
        {
            $sku = ProductSku::find()->where(['sku_name' => $sku_name])->one();
            if($sku)
            {
                $sku->common_sku_name = $sku_1c_code;
                if ($sku->save()) $success[] = $sku_name;
                else $fail[] = $sku_name;
            }
            else $fail[] = $sku_name;

        }

        return [
            'success' => $success,
            'fail' => $fail,
        ];
    }

}