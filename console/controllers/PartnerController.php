<?php

namespace console\controllers;

use common\models\PartnerOrderToSend;
use common\models\order\Order;
use common\services\partner\PartnerService;
use webmaster\models\partners\Partner;
use webmaster\models\partners\PartnerOrders;
use webmaster\modules\api\partners\PartnerCRM;
use Yii;
use yii\console\Controller;

class PartnerController extends Controller
{
    public function actionSendToPartners(): void
    {
        $partners = Partner::getAllActivePartners();
        foreach ($partners as $partner){
            $strategyClassname = "webmaster\\modules\\api\\partners\\strategy\\".$partner->class_name;
            (new PartnerCRM(new $strategyClassname()))->sendToPartner($partner->advert_id);
        }
    }
}