<?php

namespace webmaster\modules\api\controllers;

use common\models\finance\Currency;
use common\models\flow\Flow;
use common\models\geo\Geo;
use common\models\landing\OfferGeoPrice;
use Yii;
use yii\base\Behavior;
use common\modules\user\models\tables\User;
use webmaster\models\api\ApiScript;

class ApiScriptController extends BehaviorController
{
    public $modelClass = 'webmaster\models\api\ApiScript';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'new' => ['post'],
            'offer-info' => ['get'],
        ]);
        $behaviors['authenticator']['except'] = array_merge($behaviors['authenticator']['except'], [
            'offer-info'
        ]);
        return $behaviors;
    }

    public function actionNew() {

    	$scriptModel = new ApiScript();
    	$dataPHP = ApiScript::getContextPHPScript(Yii::$app->request->post());
        $dataJS = ApiScript::getContextJSScript(Yii::$app->request->post());

    	$filePath = Yii::getAlias('@webmaster').'/temp';
    	$tempPHPScriptName = time().Yii::$app->user->identity->getId().'-temp.php';
        $tempJSScriptName = time().Yii::$app->user->identity->getId().'-temp.js';

    	if(is_dir($filePath)){
    		file_put_contents($filePath.'/'.$tempPHPScriptName, $dataPHP);
            file_put_contents($filePath.'/'.$tempJSScriptName, $dataJS);
    	} else {
    		mkdir($filePath);
    		file_put_contents($filePath.'/'.$tempPHPScriptName, $dataPHP);
            file_put_contents($filePath.'/'.$tempJSScriptName, $dataJS);
    	}

        $file = $filePath.'/'.microtime().'.zip';
        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile($filePath.'/'.$tempPHPScriptName, 'api.php');
        $zip->addFile($filePath.'/'.$tempJSScriptName, 'api.js');
        $zip->close();

        unlink($filePath.'/'.$tempPHPScriptName);
        unlink($filePath.'/'.$tempJSScriptName);

        if (file_exists($file)) {
            \Yii::$app->response->sendFile($file, 'archive.zip');
            ignore_user_abort(true);
            if (connection_aborted()) unlink($file);
            register_shutdown_function('unlink', $file);
        }
    }

    public function actionOfferInfo()
    {
        $request = Yii::$app->request;
        if ($request->isGet) {
            $flow_key = $request->get('flow_key');
            $geo_iso = $request->get('geo_iso');
            $offer_id = Flow::find()->where(['flow_key' => $flow_key])->one()->offer_id;
            $geo = Geo::getGeoByIso($geo_iso);

            $respData = OfferGeoPrice::find()->where(['offer_id' => $offer_id])->andWhere(['geo_id' => $geo['geo_id']])->one();

            $response = Yii::$app->response;
            $response->data = [
                'discount' => $respData->discount,
                'new_price' => $respData->new_price,
                'old_price' => $respData->old_price,
                'phone_num_count' => $geo['phone_num_count'],
                'currency' => Currency::find()->where(['currency_id' => $respData->currency_id])->one()->currency_name
            ];
            $response->send();
        }
    }
}