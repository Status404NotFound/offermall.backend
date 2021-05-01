<?php

namespace console\controllers;

use Yii;
use common\models\landing\Landing;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertDailyRestLog;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\services\cache\CacheCommonSrv;
use common\services\ValidateException;
use crm\services\callcenter\OperatorDailySrv;
use yii\console\Controller;
use yii\db\Exception;
use linslin\yii2\curl\Curl;

class DaemonController extends Controller
{
    public function actionResetRests()
    {
        $groupLimits = TargetAdvertGroup::find()
            ->select(['target_advert_group_id', 'daily_limit'])
            ->indexBy('target_advert_group_id')
            ->asArray()
            ->all();
        $targetAdverts = TargetAdvert::find()->all();
        foreach ($targetAdverts as $advert) {
            /** @var TargetAdvert $advert */
            $tx = \Yii::$app->db->beginTransaction();
            try {
                $targetAdvertDailyRest = $advert->targetAdvertDailyRest;
                $targetAdvertDailyRestLog = new TargetAdvertDailyRestLog();
                $targetAdvertDailyRestLog->setAttributes([
                    'target_advert_id' => $targetAdvertDailyRest->target_advert_id,
                    'rest' => $targetAdvertDailyRest->rest
                ]);
                if (!$targetAdvertDailyRestLog->save()) throw new ValidateException($targetAdvertDailyRestLog->errors);
                $targetAdvertDailyRest->rest = $groupLimits[$advert->target_advert_group_id]['daily_limit'];
                if (!$targetAdvertDailyRest->save()) throw new ValidateException($targetAdvertDailyRest->errors);
                $tx->commit();
            } catch (Exception $e) {
                $tx->rollBack();
                // TODO: \Yii::error($e->getMessage(), 'db');
                throw $e;
            }
        }
    }

    public function actionCheckLandings()
    {
        $landings = Landing::getAllOfferLandings();
        $curl = new Curl();
        $errors = [];
        foreach ($landings as $landing) {
            try {
                $curl->head($landing);
                $info = $curl->getInfo();
                if (preg_match('([4-9]{1}\d\d)', $info['http_code'])) {
                    $error = [
                        'url' => $info['url'],
                        'code' => $info['http_code']
                    ];

                    $errors[] = implode(' : ', $error);
                }
            } catch (Exception $e) {
                $error = [
                    'url' => $landing,
                    'code' => 'Host not found'
                ];
                $errors[] = implode(' : ', $error);
            }
        }
        if (empty($errors)) {
            echo 'No errors';
        } else {
            $text = '';
            foreach ($errors as $error) {
                $text .= $error . '; ';
            }

            Yii::$app->serviceSms->send($text, Yii::$app->params['mega_admin_phone_for_status']);
        }
    }

    public function actionSaveDailyOperatorActivity()
    {
        $operatorDailySrv = new OperatorDailySrv();
        $operatorDailySrv->addDailyOperatorActivity();
    }

    public function actionDeleteDeliveryDates()
    {
        $sql = "DELETE FROM `delivery_date` WHERE `delivery_dates` < now()";
        return Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionRedis()
    {
        $cache = new CacheCommonSrv();
        $data = $cache->getExecutedRecordsByKeyPart('a');
        $data = json_encode($data);
        file_put_contents('redis.txt', $data);
    }
}