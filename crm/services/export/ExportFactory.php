<?php

namespace crm\services\export;

use crm\models\finstrip\calendar\FinstripExportModel;
use crm\services\export\logic\DeliveryExport;
use crm\services\export\logic\DipDropshipAeExport;
use crm\services\export\logic\FinstripExport;
use crm\services\export\logic\GroupSearchExport;
use crm\services\export\logic\IndiaBlueDartExport;
use crm\services\export\logic\OrdersExport;
use crm\services\export\logic\SuccessDeliveryExport;
use crm\services\export\logic\WfdDropshipAeExport;
use crm\services\export\logic\WfdExport;
use crm\services\export\logic\WfdFulfillmentSbExport;

/**
 * Class ExportFactory
 * @package crm\services\export
 */
class ExportFactory
{
    /**
     * @var array
     */
    private static $exportMap = [
        'orders' => OrdersExport::class,
        'group_search' => GroupSearchExport::class,
        'delivery' => DeliveryExport::class,
        'success_delivery' => SuccessDeliveryExport::class,
        'wfd' => WfdExport::class,
        'wfd_dropship_ae' => WfdDropshipAeExport::class,
        'dip_dropship_ae' => DipDropshipAeExport::class,
        'india_blue_dart' => IndiaBlueDartExport::class,
        'wfd_fulfillment_somebody' => WfdFulfillmentSbExport::class,
        'finstrip' => FinstripExport::class,
        'finstrip_summary' => FinstripExportModel::class,
    ];

    /**
     * @param $page
     * @return mixed
     */
    public static function createExport($page)
    {
        $class = self::$exportMap[$page];
        if (!$class)
            throw new \InvalidArgumentException("Export type $page not found");
        return new $class;
    }

    /**
     * @param $params
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public static function getExportSheet($params)
    {
        $sheet = \Yii::createObject([
            'class' => 'codemix\excelexport\ExcelFile',
//            'writerClass' => '\PHPExcel_Writer_Excel5', // Override default of `\PHPExcel_Writer_Excel2007`
            'writerClass' => '\PHPExcel_Writer_Excel2007',
            'sheets' => [
                $params['export_name'] => [   // Name of the excel sheet
                    'data' => $params['data'],
                    'titles' => $params['titles'],
                    'styles' => [
                        // Header
                        '1' => [
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                                'size' => 10,
                                'name' => 'Sans Serif',
                            ],
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            ],
                            'fill' => [
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        // Body
                        'A2:Z1000' => [
                            'font' => [
                                'bold' => false,
                                'color' => ['rgb' => '000000'],
                                'size' => 10,
                                'name' => 'Sans Serif'
                            ],
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            ],
                        ],
                    ],
                    'formats' => $params['formats'],
                    'formatters' => $params['formatters'],
                ],
            ],
        ]);
        return $sheet;
    }

    /**
     * @return array
     */
    public static function getDataSheet()
    {
        return [
            'export_name' => 'Default Export',
            'data' => [],
            'titles' => [],
            'formats' => [],
            'formatters' => [],
        ];
    }
}