<?php

namespace common\services\delivery;

use common\models\delivery\UserDeliveryApi;
use common\models\geo\Countries;
use common\models\geo\GeoCity;
use common\models\order\Order;
use common\modules\user\models\Permission;
use common\modules\user\models\tables\User;
use crm\services\delivery\DeliveryFactory;
use crm\services\order\logic\PushToDelivery;
use Yii;
use yii\base\Exception;

class DeliveryCommonService
{
    /**
     * @param Order $order
     * @param       $delivery_type
     * @param       $credentials
     *
     * @throws DeliveryException*@throws Exception
     * @throws Exception
     */
    public function pushToDelivery(Order $order, $delivery_type, $credentials): void
    {
        $deliveryApi = DeliveryFactory::createDelivery($delivery_type);
        $cmd = new PushToDelivery($order, $deliveryApi);

        if (!$cmd->execute($credentials)) {
            throw new DeliveryException('Failed to send Order to ' . ucfirst($delivery_type));
        }
    }

    public function getDeliveryButtons(): array
    {
        $available_apis = [];
        $user = Yii::$app->user->identity;

        if ($user->role === User::ROLE_ADVERTISER || $user->role === User::ROLE_ADVERTISER_MANAGER) {
            $permissions = (new Permission($user->role, $user->id))->permissions;

            foreach ($permissions as $code => $val) {
                if (\in_array($code, Permission::$available_apis, true)) {
                    $available_apis[] = $code;
                }
            }
        }

        $buttons = [];

        if (!empty($available_apis)) {
            $user_delivery_apis = UserDeliveryApi::find()->where(['in', 'permission_api_id', $available_apis])->all();

            foreach ($user_delivery_apis as $api) {
                /** @var UserDeliveryApi $api */
                $buttons[] = [
                    'delivery_type' => strtolower($api->deliveryApi->api_name),
                    'credentials' => strtolower($api->credentials),
                    'country_name' => $api->country->country_name,
                    'country_id' => $api->country_id,
                    'country_iso' => $api->country->country_code,
                ];
            }
        }

        return $buttons;
    }

    public function getDeliveryCountries(): array
    {
        $user_delivery_apis = UserDeliveryApi::find()
            ->select('country_id')
            ->indexBy('country_id')
            ->asArray()
            ->all();
        $countries = Countries::find()->where(['in', 'id', array_keys($user_delivery_apis)])->asArray()->all();
        foreach ($countries as &$country) {
            $country['cities'] = GeoCity::findAll(['geo_id' => $country['id']]);
        }

        return $countries;
    }
}
