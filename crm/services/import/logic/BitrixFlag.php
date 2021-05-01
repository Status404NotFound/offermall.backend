<?php

namespace crm\services\import\logic;

use common\helpers\FishHelper;
use common\models\delivery\OrderDelivery;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderCommonService;
use common\services\ValidateException;
use crm\services\import\ImportServiceException;
use Yii;

class BitrixFlag
{
    public function saveFile($base64_string)
    {
        $time = date('Y-m-d H:i:s');
        $file_path = Yii::$app->basePath . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'fetcher_cod_reports' . DIRECTORY_SEPARATOR . $time . '.xls';
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

        $order_hashes = [];
        for ($row = 2; $row < $maxRow; $row++) {
            $order_hash = (int)$file->getCell('C' . $row)->getValue();
//            $order_hash = (int)$file->getCell('A' . $row)->getValue();
            $order_hashes[] = (int)$order_hash;
        }
        return $order_hashes;
    }

    public function saveOldConversions($order_hashes_str)
    {
        $connection = new \yii\db\Connection([
            'dsn' => 'mysql:host=164.132.160.200;dbname=crmka_old',
            'username' => 'root',
            'password' => 'ronaldreig@n',
            'charset' => 'utf8',
        ]);
        $connection->open();

        $command = $connection->createCommand('SELECT count(hash_id) as cnt FROM `conversion` WHERE `conversion`.hash_id IN (' . $order_hashes_str . ');');
        $posts = $command->queryAll();
        $command = $connection->createCommand("
use crmka_old;
SET GLOBAL sql_mode = \"\";
SET SESSION sql_mode = \"\";
UPDATE `order` SET `conversion`.added_to_bitrix = 1 WHERE `conversion`.hash_id IN ($order_hashes_str);")->execute();
        return $posts;
    }

    public function saveOrders($order_hashes_str)
    {
        \Yii::$app->db->createCommand("
SET GLOBAL sql_mode = \"\";
SET SESSION sql_mode = \"\";
UPDATE `order` SET `order`.bitrix_flag = 1 WHERE `order`.order_hash IN ($order_hashes_str);")->execute();

        $command = \Yii::$app->db->createCommand('SELECT count(order_id) as cnt FROM `order` WHERE `order`.order_hash IN (' . $order_hashes_str . ');');
        $posts = $command->queryAll();
        return $posts;
    }
}
