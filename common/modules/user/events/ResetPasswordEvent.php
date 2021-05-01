<?php


namespace common\modules\user\events;

use common\modules\user\models\forms\RecoveryForm;
use common\modules\user\models\tables\Token;
use yii\base\Event;

/**
 * @property Token        $token
 * @property RecoveryForm $form
 *
 * @author makandy <makandy42@gmail.com>
 */
class ResetPasswordEvent extends Event
{
    /**
     * @var RecoveryForm
     */
    private $_form;

    /**
     * @var Token
     */
    private $_token;

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token = null)
    {
        $this->_token = $token;
    }

    /**
     * @return RecoveryForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @param RecoveryForm $form
     */
    public function setForm(RecoveryForm $form = null)
    {
        $this->_form = $form;
    }
}
