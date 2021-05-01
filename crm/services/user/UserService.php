<?php

namespace crm\services\user;

use common\models\webmaster\WmProfile;
use Yii;
use common\services\ValidateException;
use common\modules\user\models\tables\User;
use common\modules\user\models\tables\UserChild;
use common\modules\user\models\Permission;
use common\modules\user\models\tables\BaseProfile;
use common\models\finance\advert\AdvertMoney;
use common\models\callcenter\OperatorConf;
use common\services\callcenter\OperatorSettingsSrv;
use webmaster\services\notification\TelegramNotification;

/**
 * Class UserService
 * @package crm\services\user
 */
class UserService
{
    /**
     * @param $data
     * @throws ValidateException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function createUser($data)
    {
        /** @var User $user */
        $user = \Yii::createObject([
            'class' => User::className(),
            'scenario' => 'create',
        ]);
        if ($user->load($data, '')) {
            $user->password = $data['password'];
            $user->setAttributes([
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => $data['role'],
                'flags' => 0
            ]);

            if (!$user->create()) {
                throw new ValidateException($user->errors);
            }

            $user->block();


            if(isset(Yii::$app->user->id)){
                $relation = new UserChild();
                $relation->parent = Yii::$app->user->id;
                $relation->child = $user->id;

                if (!$relation->save()) {
                    throw new ValidateException($relation->errors);
                }

                if ( !empty($parent = Yii::$app->user->identity->getOwnerId()) && $parent !== Yii::$app->user->id) {
                    $relation = new UserChild();
                    $relation->parent = $parent;
                    $relation->child = $user->id;

                    if (!$relation->save()) {
                        throw new ValidateException($relation->errors);
                    }
                }
            } else {
                $wm_profile = WmProfile::findOne([
                        'wm_id' => $user->id,
                    ]) ?? new WmProfile();
                if(isset($data['telegram'])){
                    $wm_profile->telegram = $data['telegram'];
                }
                if(isset($data['skype'])){
                    $wm_profile->skype = $data['skype'];
                }
                $wm_profile->save();
            }

            $telegram = new TelegramNotification();
            $telegram->sendNewUser($user, $wm_profile);
            
            $advertMoney = new AdvertMoney();
            if ($user->role == User::ROLE_ADVERTISER && $advertMoney->load($data, '')) {

                $advertMoney->setAttributes([
                    'advert_id' => $user->id,
                    'money' => (double)0,
                    'currency_id' => $data['currency']
                ]);

//                $advertMoney->advert_id = $user->id;
//                $advertMoney->money = (double)0;
//                $advertMoney->currency_id = $data['currency'];
//                $advertMoney->last_entrance_datetime = time();

                if (!$advertMoney->save()) {
                    throw new ValidateException($advertMoney->errors);
                }
            }

            if ($user->role == User::ROLE_OPERATOR) {
                $sip = rand(1000, 1200);
                $channel = rand(800, 999);
                $operatorConf = new OperatorConf();
                $operatorConf->call_mode = OperatorSettingsSrv::MANUAL_MODE;
                $operatorConf->operator_id = $user->id;
                $operatorConf->status = 1;

                while (OperatorConf::find()->where(['sip' => $sip])->exists()) {
                    $sip = rand(1000, 1200);
                }

                while (OperatorConf::find()->where(['channel' => $channel])->exists()) {
                    $channel = rand(800, 999);
                }

                $operatorConf->sip = $sip;
                $operatorConf->channel = $channel;

                if (!$operatorConf->save()) {
                    throw new ValidateException($operatorConf->errors);
                }
            }
        }
    }

    /**
     * @param $data
     * @throws ValidateException
     * @throws \yii\db\Exception
     */
    public function updateUser($data)
    {
        $user = User::findOne(['id' => $data['id']]);
        $profile = BaseProfile::findOne(['user_id' => $data['id']]);
        $user->scenario = 'update';

        $relation = new UserChild();

        if ($user->load($data, '')) {
            $user->setAttributes([
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => $data['role_id'],
                'flags' => 0
            ]);

            $user->save();

            $profile->updateAttributes([
                'user_id' => $user->id,
                'timezone' => $data['timezone'],
                'location' => $data['location'],
                'avatar' => $data['photo'] ?? $profile->avatar,
            ]);

            if (!$profile->save()) {
                throw new ValidateException($profile->errors);
            }
        }

        if ($relation->load($data, '')) {
            $user->unlinkAll('parent', true);
            $relation->setParent($data['parent'], $user);
        }
    }

    /**
     * @param $data
     * @return bool
     * @throws ValidateException
     */
    public function saveProfile($data)
    {
        $user = User::findOne(['id' => Yii::$app->user->identity->getId()]);
        $profile = BaseProfile::findOne(['user_id' => $user['id']]);
        $user->scenario = 'update';

        if ($user->load($data, '')) {
            $user->setAttributes([
                'email' => $data['email'],
                'password' => $data['password'],
                'flags' => 0
            ]);

            if (!$user->save()) {
                throw new ValidateException($user->errors);
            }
        }

        if ($profile->load($data, '')) {
            $profile->user_id = $user->id;
            $profile->name = $data['name'];
            $profile->phone_number = $data['phone_number'];
            $profile->timezone = $data['timezone'];
            $profile->notification_audio = $data['notification_audio'];
            $profile->notification_audio_name = !empty($data['notification_audio_name']) ? $data['notification_audio_name'] : null;

            if (!empty($data['photo']) && $data['photo'] != 'default') {
                $profile->avatar = $data['photo'];
            } elseif (!empty($data['photo']) && $data['photo'] == 'default'){
                $profile->avatar = null;
            } else {
                $profile->avatar;
            }

            if (!$profile->save()) {
                throw new ValidateException($profile->errors);
            }
        }

        return true;
    }

    /**
     * @param $data
     * @throws UserServiceException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function savePermissions($data)
    {
        $user = isset($data['user']) ? User::findOne(['id' => $data['user']]) : User::findOne(['role' => $data['role']]);

        if (!$user) throw new UserServiceException('User not found!');

        $model = new Permission($user->role, $user->id);

        foreach ($data as $entity => $value) {
            if (is_array($value)) {
                foreach ($value as $permission_id => $item) {
                    if ($item == null) {
                        unset($data[$entity][$permission_id]);
                    }
                }
            }
        }

        $offer = isset($data['Offer']) ? array_keys($data['Offer']) : [];
        $order = isset($data['Order']) ? array_keys($data['Order']) : [];
        $delivery = isset($data['Delivery']) ? array_keys($data['Delivery']) : [];
        $delivery_api = isset($data['Delivery API']) ? array_keys($data['Delivery API']) : [];
        $finance = isset($data['Finance']) ? array_keys($data['Finance']) : [];
        $finstrip = isset($data['Finstrip']) ? array_keys($data['Finstrip']) : [];
        $warehouse = isset($data['Warehouse']) ? array_keys($data['Warehouse']) : [];
        $call_canter = isset($data['CallCenter']) ? array_keys($data['CallCenter']) : [];
        $call_canter_cabinet = isset($data['CallCenterCabinet']) ? array_keys($data['CallCenterCabinet']) : [];
        $webmaster = isset($data['Webmaster']) ? array_keys($data['Webmaster']) : [];
        $webmaster_cabinet = isset($data['WebmasterCabinet']) ? array_keys($data['WebmasterCabinet']) : [];
        $user = isset($data['User']) ? array_keys($data['User']) : [];
        $create_user = isset($data['CreateUser']) ? array_keys($data['CreateUser']) : [];
        $dashboard = isset($data['Dashboard']) ? array_keys($data['Dashboard']) : [];
        $role = isset($data['Roles']) ? array_keys($data['Roles']) : [];
        $export = isset($data['Export']) ? array_keys($data['Export']) : [];
        $statistics = isset($data['Statistics']) ? array_keys($data['Statistics']) : [];
        $other = isset($data['Other']) ? array_keys($data['Other']) : [];

        $model->permissions = array_merge($offer, $order, $delivery, $delivery_api, $finance, $finstrip, $warehouse, $call_canter, $call_canter_cabinet, $webmaster, $webmaster_cabinet, $user, $create_user, $dashboard, $role, $export, $statistics, $other);

        $result = empty($data['user']) ? $model->saveRolePermissions() : $model->saveUserPermissions();

        return $result;
    }

    /**
     * @param $data
     * @return bool
     * @throws UserServiceException
     */
    public function blockUser($data)
    {
        $user = User::findOne($data['id']);

        if ($user->id == Yii::$app->user->identity->getId())
            throw new UserServiceException('Error! You can not ban your own account!');

        if ($user->getIsBlocked()) {
            return $user->unblock();
        } else {
            return $user->block();
        }
    }

    /**
     * @return mixed|string
     */
    public function getUserAvatar()
    {
        $user = BaseProfile::find()
            ->select(['avatar'])
            ->where(['user_id' => Yii::$app->user->identity->getId()])
            ->one();

        if (!is_null($user)) {
            return $user->avatar;
        } else {
            $img = file_get_contents("/web/img/user_ava/avatar-default.jpg");
            $avatar = base64_encode($img);

            return $avatar;
        }
    }

    /**
     * @param $id
     * @return array|bool
     * @throws UserServiceException
     */
    public function deleteUser($id)
    {
        $user = User::findOne($id);

        if ($user->id == Yii::$app->user->identity->getId())
            throw new UserServiceException('Error! You can not remove your own account!');

        $user->flags = 1;
        $user->block();

        return $user->validate() ? $user->save() : $user->errors;
    }

    /**
     * @return array
     */
    public function getRoleData()
    {
        return User::roles();
    }
}