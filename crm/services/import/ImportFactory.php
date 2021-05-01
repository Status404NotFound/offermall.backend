<?php

namespace crm\services\import;

use crm\services\import\logic\BitrixFlag;
use crm\services\import\logic\FetcherCODReport;
use crm\services\import\logic\SkuNames;

class ImportFactory
{
    private static $reportMap = [
        'fetcher_cod_report' => FetcherCODReport::class,
        'bitrix_flag' => BitrixFlag::class,
        'sku_names' => SkuNames::class,
//        'fetcher_oman_report' => FetcherOmanReport::class
    ];

    public static function createDelivery($type)
    {
        $class = self::$reportMap[$type];
        if (!$class)
            throw new \InvalidArgumentException("Report type $type not found.");
        return new $class;
    }
}