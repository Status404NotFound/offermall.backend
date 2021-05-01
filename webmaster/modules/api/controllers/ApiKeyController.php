<?php


namespace webmaster\modules\api\controllers;


use common\helpers\FishHelper;
use webmaster\models\api\UserApi;
use common\modules\user\models\tables\User;
use Yii;
use yii\base\Behavior;

class ApiKeyController extends BehaviorController
{

    public $modelClass = 'webmaster\models\api\UserApi';

    public function behaviors() :?array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'new' => ['get'],
            'get-all-keys' => ['get'],
            'change-status' => ['post'],
            'delete-key' => ['post'],
        ]);
//        $behaviors['authenticator']['except'] = ['create-order'];
        return $behaviors;
    }

    public static function generateApiKey() :?string
    {
        $apiKeyPattern = 'xxxxxxxx-xxxx-4xxx-xxxx-xxxxxxxxxxxx';

        $result = preg_replace_callback('/x/',
            function() { return self::generateRandomString(1); }, $apiKeyPattern);

        return $result;
    }

    public static function generateRandomString($length) :?string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function actionNew()
    {
        $response = Yii::$app->response;
        $auth_header = Yii::$app->request->headers['authorization'];
        $token = substr_replace($auth_header, '',0, 7);
        $model = new UserApi();
        $user = User::findIdentityByAccessToken($token);
        $apiKey = self::generateApiKey();
        $model->setAttributes([
            'user_id' => $user->id,
            'api_key' => $apiKey,
            'status' => $model::ACTIVE_API_KEY_STATUS
        ]);
        $model->save();

        $response->data = [
            'apiKey' => $apiKey,
        ];
        $response->send();
    }

    public function actionDeleteKey()
    {
        $data = Yii::$app->request->post();
        $model = UserApi::getUserApiByApiKey($data['api_key']);
        $model->delete();
    }

    public function actionChangeStatus()
    {
        $response = Yii::$app->response;
        $data = Yii::$app->request->post();
        foreach ($data as $el){
            $model = UserApi::getUserApiByApiKey($el['api_key']);;

            $model['status'] = $el['status'];

            $model->save();

            unset($model);
        }

        $response->send();
    }

    public function actionGetAllKeys()
    {
        $response = Yii::$app->response;
        $auth_header = Yii::$app->request->headers['authorization'];
        $token = substr_replace($auth_header, '',0, 7);
        $user = User::findIdentityByAccessToken($token);
        $apiKeysModel = UserApi::getUserApiById($user->id);
        $data = [];
        foreach ($apiKeysModel as $apiKeyModel){
            array_push($data, [
                'api_key' => $apiKeyModel['api_key'],
                'status' => $apiKeyModel['status']
            ]);
        }

        $response->data = $data;

        unset($data);
        unset($user);

        $response->send();
    }
}