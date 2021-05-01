<?php


namespace common\modules\user\events;

use common\modules\user\models\tables\User;
use yii\base\Event;

/**
 * @property User $model
 *
 * @author makandy <makandy42@gmail.com>
 */
class UserEvent extends Event
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param User $form
     */
    public function setUser(User $form)
    {
        $this->_user = $form;
    }
}
