<?php
namespace webmaster\modules\wm_api\controllers;

use Yii;
use common\models\webmaster\postback\PostbackGlobal;
use common\models\webmaster\postback\PostbackIndividual;
use webmaster\services\instruments\InstrumentsService;

class InstrumentsController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\flow\Flow';

    /**
     * @var InstrumentsService
     */
    private $instrumentService;

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
     * InstrumentsController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->instrumentService = new InstrumentsService();
    }

    public function actionParkingList()
    {
        $this->response->data = $this->instrumentService->getParkedDomains();
        $this->response->send();
    }

    public function actionParkingAdd()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->instrumentService->save($request);
        $this->response->send();
    }

    public function actionParkingUrl()
    {
        $domain_id = Yii::$app->request->get('domain_id');
        $link = $this->instrumentService->getLink($domain_id);
        $this->response->data = [
            'flow_name' => $link['flow_name'],
            'flow_key' => $link['flow_key'],
            'url' => $link['domain_name'],
        ];
        $this->response->send();
    }

    public function actionParkingView()
    {
        $domain_id = Yii::$app->request->get('domain_id');
        $this->response->data = $this->instrumentService->getParkedDomainById($domain_id);
        $this->response->send();
    }

    public function actionParkingUpdate()
    {
        $request = Yii::$app->request->getBodyParams();
        $domain_id = Yii::$app->request->get('domain_id');
        $this->response->data = $this->instrumentService->update($request, $domain_id);
        $this->response->send();
    }

    public function actionParkingDelete()
    {
        $domain_id = Yii::$app->request->get('domain_id');
        $this->response->data = $this->instrumentService->delete($domain_id);
        $this->response->send();
    }

    public function actionIndividualPostbackList()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $flows = $this->instrumentService->getIndividualPostbackList($this->getRequestFilters(), $this->getRequestPagination(),
            $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $flows['list'];
        $this->setPaginationHeaders($flows['count']['count_all']);
        $this->response->send();
    }

    public function actionGlobalPostback()
    {
        $uid = Yii::$app->user->identity->getId();
        $this->response->data = (new PostbackGlobal())->getGlobalPostback($uid);
        $this->response->send();
    }

    public function actionIndividualPostback()
    {
        $uid = Yii::$app->user->identity->getId();
        $flow_id = Yii::$app->request->get('flow_id');
        $this->response->data = (new PostbackIndividual())->getIndividualPostback($flow_id, $uid);
        $this->response->send();
    }

    public function actionGlobalPostbackSave()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->instrumentService->saveGlobalPostback($request);
        $this->response->send();
    }

    public function actionIndividualPostbackSave()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->instrumentService->saveIndividualPostback($request);
        $this->response->data = $this->instrumentService->getIndividualPostbackList();
        $this->response->send();
    }

    public function actionIndividualPostbackDelete()
    {
        $postback_individual_id = Yii::$app->request->get('postback_individual_id');
        $this->instrumentService->deletePostback($postback_individual_id);
        $this->response->data = $this->instrumentService->getIndividualPostbackList();
        $this->response->send();
    }
}