<?php

namespace webmaster\services\profile;

use Yii;
use common\modules\user\models\tables\User;
use common\modules\user\models\tables\BaseProfile;
use common\models\webmaster\WmProfile;

/**
 * Class ProfileService
 * @package webmaster\services\profile
 */
class ProfileService
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return array|BaseProfile|null|\yii\db\ActiveRecord
     * @throws ProfileNotFoundException
     */
    public function getWebmasterProfileData()
    {
        $wm_id = Yii::$app->user->identity->getId();

        $query = BaseProfile::find()
            ->select([
                'base_profile.name',
                'base_profile.phone_number',
                'base_profile.avatar',
                'base_profile.timezone',
                'user.email',
                'user.created_at',
                'wm_profile.card',
                'wm_profile.facebook',
                'wm_profile.skype',
                'wm_profile.telegram',
            ])
            ->leftJoin('user', 'user.id = base_profile.user_id')
            ->leftJoin('wm_profile', 'user.id = wm_profile.wm_id')
            ->where(['user.id' => $wm_id])
            ->asArray()
            ->one();

        $query['confirmed_at'] = 'Confirmed at ' . $query['created_at'];

        return $query;
    }

    /**
     * @param $request
     * @throws ProfileNotFoundException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveProfile($request)
    {
        $wm_id = Yii::$app->user->identity->getId();
        $profile = $this->findUserMainProfile($wm_id);

        $wm_profile = $this->findWmProfile($wm_id) ?? new WmProfile();

        if (!$wm_profile || !$profile) {
            throw new ProfileNotFoundException('Webmaster not found');
        }

        $profile->timezone = $request['timezone'];
        $profile->update();

        if(isset($request['skype'])){
            $wm_profile->skype = $request['skype'];
        }
        if(isset($request['telegram'])){
            $wm_profile->telegram = $request['telegram'];
        }
        if(isset($request['facebook'])){
            $wm_profile->facebook = $request['facebook'];
        }
        if(isset($request['card'])){
            $wm_profile->card = $request['card'];
        }

        if (!$wm_profile->save())
            throw new ProfileNotFoundException($wm_profile->errors);
    }

    /**
     * @param $request
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveAvatar($request)
    {
        $user = User::findOne(['id' => Yii::$app->user->identity->getId()]);
        $profile = BaseProfile::findOne(['user_id' => $user['id']]);

        $image = './img/user_ava/avatar-default.jpg';
        $type = pathinfo($image, PATHINFO_EXTENSION);
        $data = file_get_contents($image);
        $default_image = 'data:image/' . $type . ';base64,' . base64_encode($data);

        if ($profile->load($request, '')) {
            if (!empty($request['photo']) && $request['photo'] != 'default') {
                $profile->avatar = $request['photo'];
            } elseif (!empty($request['photo']) && $request['photo'] == 'default') {
                $profile->avatar = $default_image;
            } else {
                $profile->avatar;
            }
            $profile->update();
        }

        return true;
    }

    /**
     * @param $data
     * @throws ProfileNotFoundException
     */
    public function changeUserPassword($data)
    {
        $user = User::findOne(['id' => Yii::$app->user->identity->getId()]);
        $user->scenario = 'update';

        $new = isset($data['password']) ?? false;
        $reply = isset($data['reply_password']) ?? false;

        if ($new === $reply) {
            if ($user->load($data, '')) {
                $user->setAttributes([
                    'password' => $data['password'],
                    'flags' => 0
                ]);
            }
        }

        if (!$user->save())
            throw new ProfileNotFoundException($user->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    private function findUserMainProfile($user_id)
    {
        return BaseProfile::findOne([
            'user_id' => $user_id,
        ]);
    }

    private function findWmProfile($wm_id)
    {
        return WmProfile::findOne([
            'wm_id' => $wm_id,
        ]);
    }
}