<?php


namespace webmaster\modules\wm_api\controllers;

use common\models\landing\Landing;
use common\modules\user\models\tables\User;
use common\models\offer\Offer;
use common\models\flow\Flow;
use Yii;
use webmaster\models\parking\DomainParking;
use yii\base\ErrorException;
use GuzzleHttp\Psr7\Request;
use yii\base\Exception;


class DomainController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'webmaster\models\parking\DomainParking';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'parking' => ['post'],
            'list' => ['get'],
            'exist-domains' => ['get']
        ]);
        return $behaviors;
    }

    public function actionParking()
    {
        $data = Yii::$app->request->post();
        $landingUrl = Landing::getUrlLandingById($data['landingId']);
        if (Landing::checkExistByUrl('http://'.$data['domainName'])){
            throw new Exception('Landing url is already exist!!!');
        } else {
            $landingModel = new Landing();
            $landingModel->wm_id = self::getUser()->id;
            $landingModel->name = Landing::generateLandingName();
            $landingModel->url = 'http://'.$data['domainName'];
            $landingModel->offer_id = $data['offerId'];
            $landingModel->save();
        }
        $userId = self::getUser()->id;
        $domainModel = new DomainParking();
        $domainModel->wm_id = $userId;
        $domainModel->flow_id = $data['flowId'];
        $domainModel->flow_name = Flow::findOne($data['flowId'])->flow_name;
        $domainModel->name = $data['domainName'];
        $domainModel->landing_id = $data['landingId'];
        $domainModel->landing_url = $landingUrl;
        $domainModel->offer_id = $data['offerId'];
        $domainModel->offer_name = Offer::findOne($data['offerId'])->offer_name;

        switch ($data['mode']) {
            case 'ExistDomain':
                $domainModel->mode = $domainModel::EXIST_DOMAIN_MODE;
                break;
            case 'OwnDomain':
                $domainModel->mode = $domainModel::OWN_DOMAIN_MODE;
                break;
            default:
                break;
        }

        $domainModel->save();

        $landingDomain = preg_replace('#^https?://#', '', $landingUrl);

        $params = ['landingName' => $landingDomain, 'domainName' => $data['domainName']];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://parking.shopiums.net");
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        $response = Yii::$app->response;

        $response->data = [
            'domains' => DomainParking::getAllUserDomains($userId),
            'parkingServerResponse' => $server_output
        ];
        $response->send();

    }

   public function actionList(){
       $response = Yii::$app->response;
       $domains = DomainParking::getAllUserDomains(self::getUser()->id);
       $response->data = $domains;
       $response->send();
   }

    private static function getUser()
    {
        $auth_header = Yii::$app->request->headers['authorization'];
        $token = substr_replace($auth_header, '', 0, 7);
        return User::findIdentityByAccessToken($token);
    }

    public function actionExistDomains()
    {
        $domainsStr = file_get_contents('/var/www/crmka.net/crmka.net-prod-back/webmaster/domains');
        $domains = explode(';', $domainsStr);
        $response = Yii::$app->response;
        $response->data = $domains;
        $response->send();
    }

}