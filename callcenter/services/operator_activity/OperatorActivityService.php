<?php

namespace callcenter\services\operator_activity;

use common\services\callcenter\OperatorSettingsSrv;
use Yii;
use yii\base\Model;
use common\services\cache\CacheCommonSrv;
use common\models\callcenter\OperatorActivity;

class OperatorActivityService extends Model
{
    public function addActivity($params)
    {
//        if ($params['status'] == OperatorSettingsSrv::STATUS_ONLINE) Yii::$app->operator->setOperatorStatus($status_id);
        Yii::$app->operator->setOperatorStatus($params['status']);

        $cacheSrv = new CacheCommonSrv();
        $fields = [
            'table' => 'operator_activity',
            [
                'field' => 'datetime',
                'value' => date('Y-m-d H:i:s'),
            ],
            [
                'field' => 'operator_id',
                'value' => Yii::$app->operator->is_config_operator ? Yii::$app->operator->id : $params['operator_id'],
            ],
        ];
        
        $cacheSrv->generateKeyFromArray($fields);
        $data = [
            'table'           => 'operator_activity',
            'is_approved'     => true,
            'operator_status' => $params['status'],
            'action'          => $params['action'],
            'datetime'        => date('Y-m-d H:i:s'),
            'process'         => $params['process'] == 'auto' ? OperatorActivity::PROCESS_AUTO : OperatorActivity::PROCESS_MANUAL,
            'operator_id'     => Yii::$app->operator->is_config_operator ? Yii::$app->operator->id : $params['operator_id'],
        ];
    
        $cacheSrv->set($data);
    }
}