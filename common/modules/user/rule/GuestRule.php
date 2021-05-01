<?php

namespace common\modules\user\rule;

use yii\rbac\Rule;

/**
 * Description of GuestRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.5
 */
class GuestRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'guest_rule';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        /** @var \yii\web\User $user */
//        $user->getIsGuest();
        echo '<pre>' . print_r(is_null($user), true) . '</pre>';
        return false;
    }
}
