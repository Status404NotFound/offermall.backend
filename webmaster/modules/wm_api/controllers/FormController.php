<?php


namespace webmaster\modules\wm_api\controllers;

use common\models\landing\Landing;
use Yii;
use common\modules\user\models\tables\User;
use webmaster\models\form\Form;

class FormController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'webmaster\models\form\Form';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'generate-form' => ['post'],
            'list' => ['get'],
            'delete-form' => ['post']
        ]);
        return $behaviors;
    }

    public function actionGenerateForm()
    {
        $data = Yii::$app->request->post();

        if (Landing::checkExistByUrl($data['url'])){
            $landingModel = Landing::getLandingByUrl($data['url']);
        } else {
            $landingModel = new Landing();
            $landingModel->wm_id = self::getUser()->id;
            $landingModel->form_id = 29;
        }
        $landingModel->name = Landing::generateLandingName();
        $landingModel->url = $data['url'];
        $landingModel->offer_id = $data['offerId'];
        $landingModel->save();
        if (Form::checkExistByUrl($data['url'])){
            $formModel = Form::getFormByUrl($data['url']);
        } else {
            $formModel = new Form();
        }
        $formModel->wm_id = self::getUser()->id;
        $formModel->flow_id = $data['flowId'];
        $formModel->url = $data['url'];
        $formModel->landing_id = $landingModel->landing_id;

        $formModel->flow_name = $data['flowName'];
        $formModel->offer_id = $data['offerId'];
        $formModel->offer_name = $data['offerName'];
        $formModel->save();

        $response = Yii::$app->response;
        $response->data = [
            'formCode' => self::getFormScript($formModel->landing_id),
            'debug' => Landing::checkExistByUrl($data['url'])
        ];
        $response->send();
    }

    public function actionList()
    {
        $user = self::getUser();
        $data = Form::getFormsByUserId($user->id);
        $response = Yii::$app->response;
        $response->data = $data;
        $response->send();
    }

    public function actionDeleteForm()
    {
        $targetId = Yii::$app->request->post()['id'];
        $form = Form::findOne($targetId);
        $form->delete();

        $user = self::getUser();
        $data = Form::getFormsByUserId($user->id);
        $response = Yii::$app->response;
        $response->data = $data;
        $response->send();
    }

    public static function getUser()
    {
        $auth_header = Yii::$app->request->headers['authorization'];
        $token = substr_replace($auth_header, '',0, 7);
        return User::findIdentityByAccessToken($token);
    }

    public static function getFormScript($landing_id) :string
    {
        return 'in head section(for default stylization): 
<link rel="stylesheet" href="http://t.crmka.net/form.css">
in body section:'.PHP_EOL.'
<div class="form_block"></div>
<script src="http://t.crmka.net/generate-form.js"></script>
<script>formInit('.$landing_id.')</script>';
    }
}