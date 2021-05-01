<?php

namespace common\modules\user\traits;

use common\modules\user\events\FormEvent;
use common\modules\user\events\ProfileEvent;
use common\modules\user\events\ResetPasswordEvent;
use common\modules\user\events\UserEvent;
use common\modules\user\models\tables\BaseProfile;
use common\modules\user\models\forms\RecoveryForm;
use common\modules\user\models\tables\Token;
use common\modules\user\models\tables\User;
use yii\base\Model;

/**
 * Trait EvenTrait
 *
 * @author makandy <makandy42@gmail.com>
 */
trait EventTrait
{
    /**
     * @param Model $form
     * @return object
     */
    protected function getFormEvent(Model $form) {
        return \Yii::createObject(['class' => FormEvent::className(), 'form' => $form]);
    }

    /**
     * @param User $user
     * @return object
     */
    protected function getUserEvent(User $user) {
        return \Yii::createObject(['class' => UserEvent::className(), 'user' => $user]);
    }

    /**
     * @param BaseProfile $profile
     * @return object
     */
    protected function getProfileEvent(BaseProfile $profile) {
        return \Yii::createObject(['class' => ProfileEvent::className(), 'profile' => $profile]);
    }

    /**
     * @param Token|null $token
     * @param RecoveryForm|null $form
     * @return object
     */
    protected function getResetPasswordEvent(Token $token = null, RecoveryForm $form = null) {
        return \Yii::createObject(['class' => ResetPasswordEvent::className(), 'token' => $token, 'form' => $form]);
    }
}
