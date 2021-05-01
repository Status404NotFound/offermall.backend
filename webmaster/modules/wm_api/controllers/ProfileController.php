<?php
namespace webmaster\modules\wm_api\controllers;

use common\models\webmaster\WmProfile;
use Yii;
use webmaster\services\profile\ProfileService;

class ProfileController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\webmaster\WmProfile';

    /**
     * @var ProfileService
     */
    private $profileService;

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
     * ProfileController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->profileService = new ProfileService();
    }

    public function actionData()
    {
        $this->response->data = $this->profileService->getWebmasterProfileData();
        $this->response->send();
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->profileService->saveProfile($request);
        $this->response->send();
    }

    public function actionUploadAvatar()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->profileService->saveAvatar($request);
        $this->response->send();
    }

    public function actionChangePassword()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->profileService->changeUserPassword($request);
        $this->response->send();
    }
}