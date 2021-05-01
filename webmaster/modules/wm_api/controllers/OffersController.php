<?php

namespace webmaster\modules\wm_api\controllers;

use common\models\offer\Offer;
use Yii;
use webmaster\services\offer\OfferSearch;
use webmaster\services\offer\OfferService;

class OffersController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\offer\Offer';

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
        ]);
        return $behaviors;
    }

    /**
     * OffersController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->offerService = new OfferService();
    }

    public function actionOfferList()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $offers = $this->offerService->findOffers($this->getRequestFilters(), $this->getRequestPagination(), $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $offers['offers'];
        $this->setPaginationHeaders($offers['count']['count_all']);
        $this->response->send();
    }

    public function actionOfferInfo()
    {
        $offer_id = Yii::$app->request->get('id');
        $this->response->data = (new OfferSearch())->getOfferInfoData($offer_id);
        $this->response->send();
    }

    public function actionTakeOffer($id)
    {
        $post = Yii::$app->request->post();
        $this->response->data = $this->offerService->saveOfferData($id, $post);
        $this->response->send();
    }
}