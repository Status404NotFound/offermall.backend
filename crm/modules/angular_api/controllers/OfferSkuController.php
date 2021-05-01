<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\models\product\SkuView;
use crm\services\offer\OfferSkuService;
use crm\services\targets\logic\AdvertTargetDataProvider;
use crm\services\targets\AdvertTargetService;
use Yii;

class OfferSkuController extends BehaviorController
{
    public $modelClass = 'common\models\offer\OfferSku';

    private $offerSkuService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->offerSkuService = new OfferSkuService();
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {

    }

    public function actionCreate()
    {
        $offer_id = Yii::$app->request->post('offer_id');
        $offer_skus = Yii::$app->request->post('offer_skus');
        $this->response->data = $this->offerSkuService->create($offer_id, $offer_skus);
        $this->response->send();
    }

    public function actionView()
    {
        $offer_id = Yii::$app->request->get('offer_id');
        $product_id_array = Offer::findProductIds($offer_id);
        $this->response->data = [
            'targets' => (new AdvertTargetService())->getAdvertTargetData($offer_id, AdvertTargetDataProvider::SKU_TAB),
            'sku_list' => SkuView::find()->select(['sku_id', 'sku_name', 'geo_id', 'advert_id'])
                ->where(['product_id' => $product_id_array])->asArray()->all(),
//            'sku_rules' => $this->offerSkuService->getOfferSkuRules($offer_id)
        ];
        $this->response->send();
    }
}