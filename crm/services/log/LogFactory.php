<?php

namespace crm\services\log;

use crm\services\log\logic\OrderLogService;
use crm\services\log\logic\OrderSkuLogService;

class LogFactory
{
    private static $logMap = [
        'orderSku' => OrderSkuLogService::class,
        'order' => OrderLogService::class,
    ];

    public static function createLog($type)
    {
        $class = self::$logMap[$type];
        if (!$class) throw new \InvalidArgumentException("Log type $type not found.");
        return new $class;
    }
}