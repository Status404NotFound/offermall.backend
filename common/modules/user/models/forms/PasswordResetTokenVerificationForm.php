<?php
    namespace common\modules\user\models\forms;

    use yii\base\Model;
    use yii\base\InvalidParamException;
    use common\modules\user\models\tables\User;

    /**
     * Password reset token verification form
     */
    class PasswordResetTokenVerificationForm extends Model
    {
        /**
         * @var \common\modules\user\models\tables\User
         */
        public $token;


        /**
         * @var
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

    }