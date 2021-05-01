<?php

namespace common\modules\user\models\forms;

use yii\base\Model;
use yii\base\InvalidParamException;
use common\modules\user\models\tables\User;

/**
 * Password reset form
 */
class PasswordResetForm extends Model
{
    /**
     * @var
     */
    public $token;
    /**
     * @var
     */
    public $password;


    /**
     * @var \common\modules\user\models\tables\User
     */
    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['token', 'required'],
            ['token', 'validatePasswordResetToken'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],

        ];
    }

    /**
     * Validates the password reset token.
     * This method serves as the inline validation for password reset token.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePasswordResetToken($attribute, $params)
    {
        $this->_user = User::findByPasswordResetToken($this->$attribute);

        if (!$this->_user) {
            $this->addError($attribute, 'Incorrect password reset token.');
        }
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}